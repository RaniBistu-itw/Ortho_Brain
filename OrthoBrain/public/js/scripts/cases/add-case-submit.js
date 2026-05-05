window.AddCaseSubmit = {
  /**
   * Run validateAll() across every section that has one.
   * Returns { valid: boolean, sectionsWithErrors: string[] }
   */
  validateAll() {
    const sections = [
      { id: 'patient-information', controller: window.PatientInformationSection },
      { id: 'prescription',        controller: window.PrescriptionSection },
      // additional-information is fully optional — always valid
      // perfect-smile-plan is a placeholder — always valid
      { id: 'impressions',         controller: window.ImpressionsSection },
      { id: 'photographs',         controller: window.PhotographsSection },
      { id: 'xrays',               controller: window.XRaysSection },
      // additional-records is a placeholder — always valid
      { id: 'shipping-address',    controller: window.ShippingAddressSection },
      { id: 'submit-order',        controller: window.SubmitOrderSection },
    ]

    const sectionsWithErrors = []
    for (const { id, controller } of sections) {
      if (!controller || typeof controller.validateAll !== 'function') {
        console.warn(`[AddCaseSubmit] Section ${id} has no validateAll() method`)
        continue
      }
      const valid = controller.validateAll()
      if (!valid) sectionsWithErrors.push(id)
    }

    return { valid: sectionsWithErrors.length === 0, sectionsWithErrors }
  },

  /**
   * The full submit flow triggered by the top-bar Submit button.
   */
  async submit() {
    if (window.AddCaseState) window.AddCaseState.hasAttemptedSubmit = true

    const { valid, sectionsWithErrors } = this.validateAll()

    if (!valid) {
      this._highlightErrorSections(sectionsWithErrors)
      this._scrollToFirstErrorSection(sectionsWithErrors)
      return
    }

    // Clear any previous error indicators since all sections are now valid
    this._highlightErrorSections([])

    const confirmed = await this._showConfirmationModal()
    if (!confirmed) return

    await this._performSubmit()
  },

  /**
   * Updates window.AddCaseState.ui.sectionsWithErrors and syncs rail indicators.
   */
  _highlightErrorSections(sectionIds) {
    if (!window.AddCaseState.ui) {
      window.AddCaseState.ui = { sectionsWithErrors: [] }
    }
    window.AddCaseState.ui.sectionsWithErrors = sectionIds
    this._updateRailErrorIndicators(sectionIds)
  },

  /**
   * DOM-based rail error indicator update (Option B).
   * Adds/removes error dot and has-error class on each rail item.
   */
  _updateRailErrorIndicators(sectionIds) {
    const railItems = document.querySelectorAll('.add-case-rail__item[data-section]')
    railItems.forEach(item => {
      const sectionId = item.getAttribute('data-section')
      const hasError = sectionIds.includes(sectionId)
      item.classList.toggle('add-case-rail__item--has-error', hasError)

      const iconTile = item.querySelector('.add-case-rail__icon-tile')
      if (!iconTile) return

      let dot = iconTile.querySelector('.add-case-rail__error-dot')
      if (hasError && !dot) {
        dot = document.createElement('span')
        dot.className = 'add-case-rail__error-dot'
        dot.setAttribute('aria-label', 'Section has errors')
        iconTile.appendChild(dot)
      } else if (!hasError && dot) {
        dot.remove()
      }
    })
  },

  /**
   * Scrolls to the first section with errors, accounting for sticky top bar.
   */
  _scrollToFirstErrorSection(sectionIds) {
    if (sectionIds.length === 0) return
    const firstId = sectionIds[0]
    const el = document.getElementById(firstId)
    if (!el) return
    const topBarOffset = 80
    const elementTop = el.getBoundingClientRect().top + window.scrollY
    window.scrollTo({
      top: elementTop - topBarOffset,
      behavior: 'smooth',
    })
  },

  /**
   * Shows the submit confirmation modal.
   * Returns Promise<boolean> — true if confirmed, false if cancelled/dismissed.
   */
  _showConfirmationModal() {
    return new Promise((resolve) => {
      const modalEl = document.getElementById('submitConfirmModal')
      if (!modalEl) {
        console.error('[AddCaseSubmit] Confirmation modal element not found')
        resolve(false)
        return
      }

      const modal = new bootstrap.Modal(modalEl)
      const confirmBtn = modalEl.querySelector('[data-submit-confirm]')
      const cancelBtn  = modalEl.querySelector('[data-submit-cancel]')

      let resolved = false
      const cleanup = (result) => {
        if (resolved) return
        resolved = true
        confirmBtn.removeEventListener('click', onConfirm)
        cancelBtn.removeEventListener('click', onCancel)
        modalEl.removeEventListener('hidden.bs.modal', onHidden)
        resolve(result)
      }

      const onConfirm = () => { modal.hide(); cleanup(true)  }
      const onCancel  = () => { modal.hide(); cleanup(false) }
      const onHidden  = () => { cleanup(false) }

      confirmBtn.addEventListener('click', onConfirm)
      cancelBtn.addEventListener('click', onCancel)
      modalEl.addEventListener('hidden.bs.modal', onHidden)

      modal.show()
    })
  },

  /**
   * Final submit: flush the Prescription draft, call the submit endpoint,
   * clear local draft, set flash, redirect.
   */
  async _performSubmit() {
    const state = window.AddCaseState
    const caseId = (window.AddCaseSave && window.AddCaseSave.currentCaseId && window.AddCaseSave.currentCaseId())
      || state.caseId
      || window.CASE_ID

    try {
      // Flush any pending Prescription changes to DB first.
      if (window.AddCaseSave && typeof window.AddCaseSave.saveDraft === 'function') {
        await window.AddCaseSave.saveDraft()
      }

      const effectiveId = (window.AddCaseSave && window.AddCaseSave.currentCaseId && window.AddCaseSave.currentCaseId())
        || state.caseId
        || caseId

      if (!effectiveId || effectiveId === 'new') {
        throw new Error('Case has not been persisted yet — cannot submit.')
      }

      const initials = (state && state.submitOrder && state.submitOrder.submitterInitials) || ''
      const res = await window.CaseApi.submitCase(effectiveId, { submitter_initials: initials })

      localStorage.removeItem(`addCaseDraft:${effectiveId}`)
      localStorage.removeItem(`addCaseDraft:new`)

      sessionStorage.setItem('caseSubmittedFlash', JSON.stringify({
        message: res.message || `Case ${effectiveId} submitted successfully.`,
        type: 'success',
        timestamp: Date.now(),
      }))

      window.location.href = res.redirect || '/dev/cases'
    } catch (err) {
      console.error('[AddCaseSubmit] submit failed', err)
      // submit() returns two error shapes: the older nested
      // { errors: { prescription: '...' } } and the newer top-level
      // { error: '...', message: '...' } used by the patient_id and
      // initials_required guards. Read message first so all three guards
      // surface their specific copy. (Side effect: the patient_required
      // 422 from PR #96 was previously falling through to the generic
      // alert — this fixes that too.)
      const data = (err && err.data) || {}
      const msg = data.message
        || (data.errors && data.errors.prescription)
        || 'Submit failed. Check the form and try again.'
      alert(msg)

      if (data.error === 'initials_required') {
        const sec = document.getElementById('submit-order')
        if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' })
        const input = document.getElementById('so-initials')
        if (input) setTimeout(() => input.focus(), 400)
      }
    }
  },
}
