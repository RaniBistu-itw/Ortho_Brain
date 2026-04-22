(function () {
  'use strict';

  var DB_NAME = 'OrthoBrainDrafts';
  var DB_VERSION = 1;
  var STORE = 'caseImages';

  var _dbPromise = null;

  function openDb() {
    if (_dbPromise) return _dbPromise;
    _dbPromise = new Promise(function (resolve, reject) {
      var req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = function (e) {
        var db = e.target.result;
        if (!db.objectStoreNames.contains(STORE)) {
          var store = db.createObjectStore(STORE, { keyPath: ['caseId', 'section', 'tileId'] });
          store.createIndex('byCase', 'caseId', { unique: false });
        }
      };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror   = function () { reject(req.error); };
    });
    return _dbPromise;
  }

  function tx(mode) {
    return openDb().then(function (db) {
      return db.transaction(STORE, mode).objectStore(STORE);
    });
  }

  function asPromise(req) {
    return new Promise(function (resolve, reject) {
      req.onsuccess = function () { resolve(req.result); };
      req.onerror   = function () { reject(req.error); };
    });
  }

  // TODO: replace with presigned S3 PUT — see CLAUDE.md "Production swap" notes.
  // The interface below stays the same; only the storage backend changes.
  window.CaseImageStore = {

    put: function (caseId, section, tileId, blob) {
      return tx('readwrite').then(function (store) {
        return asPromise(store.put({
          caseId:  String(caseId),
          section: String(section),
          tileId:  String(tileId),
          blob:    blob,
          updatedAt: Date.now(),
        }));
      });
    },

    get: function (caseId, section, tileId) {
      return tx('readonly').then(function (store) {
        return asPromise(store.get([String(caseId), String(section), String(tileId)]));
      }).then(function (row) {
        return row ? row.blob : null;
      });
    },

    remove: function (caseId, section, tileId) {
      return tx('readwrite').then(function (store) {
        return asPromise(store.delete([String(caseId), String(section), String(tileId)]));
      });
    },

    listForCase: function (caseId) {
      return tx('readonly').then(function (store) {
        var idx = store.index('byCase');
        return asPromise(idx.getAll(String(caseId)));
      }).then(function (rows) {
        return rows || [];
      });
    },

    clearCase: function (caseId) {
      var self = this;
      return self.listForCase(caseId).then(function (rows) {
        return tx('readwrite').then(function (store) {
          return Promise.all(rows.map(function (row) {
            return asPromise(store.delete([row.caseId, row.section, row.tileId]));
          }));
        });
      });
    },

    // Migrate all rows from oldCaseId → newCaseId. Used when add-case.js
    // calls ensureShellCreated() and the placeholder 'new' becomes a real ID.
    rekey: function (oldCaseId, newCaseId) {
      var self = this;
      return self.listForCase(oldCaseId).then(function (rows) {
        if (!rows.length) return;
        return tx('readwrite').then(function (store) {
          var ops = [];
          rows.forEach(function (row) {
            ops.push(asPromise(store.put({
              caseId:    String(newCaseId),
              section:   row.section,
              tileId:    row.tileId,
              blob:      row.blob,
              updatedAt: Date.now(),
            })));
            ops.push(asPromise(store.delete([row.caseId, row.section, row.tileId])));
          });
          return Promise.all(ops);
        });
      });
    },
  };
})();
