window.MediaTileHelpers = {
  // Returns { valid: boolean, error: string | null }
  validateFile: function (file, options) {
    var maxSizeBytes = options.maxSizeBytes;
    var allowedMimeTypes = options.allowedMimeTypes;
    var formatError = options.formatError || 'Invalid file format.';

    if (file.size > maxSizeBytes) {
      return { valid: false, error: 'File exceeds ' + Math.round(maxSizeBytes / 1024 / 1024) + 'MB limit.' };
    }

    // HEIC files often report empty MIME type in browsers; check extension too
    var fileName = file.name.toLowerCase();
    var isHeic = fileName.endsWith('.heic') || fileName.endsWith('.heif') || file.type === 'image/heic';
    if (isHeic && allowedMimeTypes.includes('image/heic')) {
      return { valid: true, error: null };
    }
    if (!allowedMimeTypes.includes(file.type)) {
      return { valid: false, error: formatError };
    }
    return { valid: true, error: null };
  },

  // Convert HEIC file to JPEG blob for preview
  convertHeicForPreview: async function (file) {
    try {
      var result = await window.heic2any({
        blob: file,
        toType: 'image/jpeg',
        quality: 0.85,
      });
      // heic2any returns Blob or Blob[]; normalize to single Blob
      return Array.isArray(result) ? result[0] : result;
    } catch (err) {
      console.error('HEIC conversion failed:', err);
      throw err;
    }
  },

  // Create an object URL, handling HEIC conversion first if needed.
  // Returns { url: string, cleanup: () => void }
  createPreviewUrl: async function (file) {
    var fileName = file.name.toLowerCase();
    var isHeic = fileName.endsWith('.heic') || fileName.endsWith('.heif') || file.type === 'image/heic';

    var blobForPreview = file;
    if (isHeic) {
      blobForPreview = await this.convertHeicForPreview(file);
    }

    var url = URL.createObjectURL(blobForPreview);
    return {
      url: url,
      cleanup: function () { URL.revokeObjectURL(url); },
    };
  },
};
