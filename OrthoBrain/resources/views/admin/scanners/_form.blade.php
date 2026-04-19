@csrf
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">Scanner Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $scanner->name) }}" placeholder="Enter scanner name"
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="portal_link" class="form-label">Scanner Portal Link</label>
        <input id="portal_link" name="portal_link" type="url" maxlength="255" value="{{ old('portal_link', $scanner->portal_link) }}" placeholder="https://www.example.com"
               class="form-control @error('portal_link') is-invalid @enderror">
        @error('portal_link')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="portal_password" class="form-label">Portal Password</label>
        <div class="input-group input-group-merge form-password-toggle">
            <input id="portal_password" name="portal_password" type="password" maxlength="255" value="{{ old('portal_password', $scanner->portal_password) }}" placeholder="••••••••" class="form-control">
            <span class="input-group-text cursor-pointer" onclick="const i=document.getElementById('portal_password'); i.type = i.type==='password' ? 'text' : 'password';">
                <i data-feather="eye"></i>
            </span>
        </div>
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $scanner->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $scanner->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
    <div class="col-12 mb-1">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Description goes here" class="form-control">{{ old('description', $scanner->description) }}</textarea>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.scanners.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
