  (function(){
    const calleSelect = document.getElementById('calle_id');
    const cdraSelect = document.getElementById('cdraSelect');
    const dir1Input = document.getElementById('dir1Input');
    if (!calleSelect || !cdraSelect) return;

    function buildCdraOptions(ini, fin) {
      cdraSelect.innerHTML = '<option value="">-- Selecciona cuadra --</option>';
      if (!Number.isFinite(ini) || !Number.isFinite(fin)) { return; }
      const selected = parseInt(cdraSelect.dataset.selected || '', 10);
      for (let i = ini; i <= fin; i++) {
        const opt = document.createElement('option');
        opt.value = String(i);
        opt.textContent = 'C' + i;
        if (!Number.isNaN(selected) && selected === i) {
          opt.selected = true;
        }
        cdraSelect.appendChild(opt);
      }
    }

    function handleCalleChange() {
      const opt = calleSelect.options[calleSelect.selectedIndex];
      const ini = opt ? parseInt(opt.dataset.cdra_ini || '', 10) : NaN;
      const fin = opt ? parseInt(opt.dataset.cdra_fin || '', 10) : NaN;
      buildCdraOptions(ini, fin);
      if (dir1Input) {
        dir1Input.value = opt ? (opt.dataset.nombre || '') : '';
      }
      const selected = parseInt(cdraSelect.dataset.selected || '', 10);
      if (Number.isNaN(ini) || Number.isNaN(fin) || selected < ini || selected > fin) {
        cdraSelect.dataset.selected = '';
      }
    }

    calleSelect.addEventListener('change', function(){
      cdraSelect.dataset.selected = '';
      handleCalleChange();
    });

    handleCalleChange();
  })();