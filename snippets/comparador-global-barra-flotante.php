add_action('wp_footer', 'mp_barra_comparador_global_pcf');

function mp_barra_comparador_global_pcf() {
    ?>
    <div id="mp-notif-container"></div>

    <div id="mp-compare-bar" class="mp-compare-bar">
        <button id="mp-comp-close" class="mp-comp-close-btn" title="Ocultar panel">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        
        <div class="mp-compare-container">
            <div class="mp-compare-header">
                <span class="mp-ch-title">Comparador</span>
                <span class="mp-ch-subtitle"><strong id="mp-comp-count">0</strong> de 3 productos</span>
            </div>
            
            <div id="mp-comp-slots" class="mp-comp-slots"></div>
            
            <div class="mp-comp-actions">
                <button id="mp-comp-btn" disabled>Comparar</button>
                <span id="mp-comp-clear">Limpiar selección</span>
            </div>
        </div>
    </div>

    <style>
        #mp-notif-container { position: fixed; top: 40px; left: 50%; transform: translateX(-50%); z-index: 9999999; display: flex; flex-direction: column; gap: 10px; align-items: center; pointer-events: none; }
        .mp-notif { background: #1e293b; color: #fff; padding: 12px 25px; border-radius: 50px; font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; opacity: 0; transform: translateY(-20px); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .mp-notif.show { opacity: 1; transform: translateY(0); }
        .mp-notif-error { background: #e53e3e; } /* Rojo para errores */
        .mp-notif svg { width: 18px; height: 18px; }

        .mp-compare-bar { position: fixed; bottom: -350px; left: 0; width: 100%;background-color: rgba(30, 41, 59, 0.8) !important; z-index: 999999; box-shadow: 0 -5px 25px rgba(0,0,0,0.4); transition: bottom 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); font-family: 'Montserrat', Arial, sans-serif; border-top: 4px solid #4683b2; }
        .mp-compare-bar.show { bottom: 0; }
        .mp-compare-container { max-width: 1250px; margin: 0 auto; padding: 25px 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        
        .mp-comp-close-btn { position: absolute; top: -18px; right: 25px; background: #334155; border: 3px solid #1e293b; color: #94a3b8; border-radius: 50%; width: 36px; height: 36px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s; padding: 0; box-shadow: 0 -2px 10px rgba(0,0,0,0.2); }
        .mp-comp-close-btn:hover { background: #e53e3e; color: #fff; }
        .mp-comp-close-btn svg { width: 16px; height: 16px; }

        .mp-compare-header { display: flex; flex-direction: column; width: 160px; color: #fff; }
        .mp-ch-title { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .mp-ch-subtitle { font-size: 13px; color: #cbd5e1; font-weight: 500; }
        .mp-ch-subtitle strong { font-weight: 800; color: #fff; }

        .mp-comp-slots { display: flex; gap: 20px; flex-grow: 1; justify-content: center; }
        
        .mp-comp-item { background: #ffffff; border-radius: 6px; padding: 15px; display: flex; flex-direction: column; align-items: flex-start; width: 290px; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; transition: transform 0.2s; }
        .mp-comp-item:hover { transform: translateY(-3px); }
        
        .mp-comp-item-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 34px; white-space: normal; }
        
        .mp-comp-item-body { display: flex; width: 100%; gap: 15px; align-items: center; }
        .mp-comp-item img { width: 65px; height: 65px; object-fit: contain; }
        .mp-comp-item-prices { display: flex; flex-direction: column; flex-grow: 1; }
        
        .mp-comp-item-prices .woocommerce-Price-amount { font-size: 16px !important; font-weight: 800 !important; color: #111 !important; line-height: 1.1; display: block; }
        .mp-comp-item-lbl { font-size: 11px; color: #64748b; margin-top: 4px; font-weight: 500; }
        
        .mp-comp-item-remove { position: absolute; top: -10px; right: -10px; background: #23c16b; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: background 0.2s, transform 0.2s; padding: 0; }
        .mp-comp-item-remove:hover { background: #1fa75c; transform: scale(1.1); }
        .mp-comp-item-remove svg { width: 14px; height: 14px; pointer-events: none;}

        .mp-comp-actions { display: flex; flex-direction: column; align-items: center; gap: 12px; min-width: 170px; }
        #mp-comp-btn { background: #23c16b; color: #fff; border: none; padding: 14px 20px; border-radius: 4px; font-weight: 800; font-size: 15px; cursor: pointer; width: 100%; transition: background 0.3s ease; }
        #mp-comp-btn:hover:not(:disabled) { background: #1fa75c; }
        #mp-comp-btn:disabled { background: #334155; color: #64748b; cursor: not-allowed; }
        
        #mp-comp-clear { color: #94a3b8; font-size: 13px; text-decoration: underline; cursor: pointer; font-weight: 600; transition: color 0.2s; }
        #mp-comp-clear:hover { color: #fff; }

        @media (max-width: 1024px) {
            .mp-compare-container { flex-wrap: wrap; justify-content: center; padding: 15px; }
            .mp-compare-header { width: 100%; flex-direction: row; justify-content: space-between; align-items: center; padding: 0 10px; margin-bottom: 10px; }
            .mp-comp-slots { width: 100%; overflow-x: auto; justify-content: flex-start; padding: 10px 0; }
            .mp-comp-item { min-width: 260px; }
            .mp-comp-actions { width: 100%; flex-direction: row; justify-content: space-between; align-items: center; padding: 0 10px; }
            #mp-comp-btn { width: auto; flex-grow: 1; }
            #mp-comp-clear { margin-left: 20px; }
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let compareList = JSON.parse(localStorage.getItem('mp_compare_list')) || [];
        const maxCompare = 3;
        
        const compareBar = document.getElementById('mp-compare-bar');
        const slotsContainer = document.getElementById('mp-comp-slots');
        const countSpan = document.getElementById('mp-comp-count');
        const btnCompare = document.getElementById('mp-comp-btn');
        const notifContainer = document.getElementById('mp-notif-container');

        function showNotif(type, message) {
            const id = Date.now();
            const isError = type === 'error';
            const icon = isError ? 
                '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' :
                '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12L11 14L15 10M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

            const html = `<div id="notif-${id}" class="mp-notif ${isError ? 'mp-notif-error' : ''}">${icon}<span>${message}</span></div>`;
            notifContainer.insertAdjacentHTML('beforeend', html);
            
            const notif = document.getElementById(`notif-${id}`);
            setTimeout(() => notif.classList.add('show'), 10);

            setTimeout(() => {
                notif.classList.remove('show');
                setTimeout(() => notif.remove(), 400); 
            }, 3000);
        }

        function updateCompareUI() {
            localStorage.setItem('mp_compare_list', JSON.stringify(compareList));
            countSpan.innerText = compareList.length;

            if(compareList.length > 0) {
                compareBar.classList.add('show');
            } else {
                compareBar.classList.remove('show');
            }

            if(compareList.length >= 2) {
                btnCompare.disabled = false;
                btnCompare.innerText = "Comparar";
            } else {
                btnCompare.disabled = true;
                btnCompare.innerText = "Comparar";
            }

            slotsContainer.innerHTML = '';
            compareList.forEach(item => {
                const html = `
                    <div class="mp-comp-item">
                        <div class="mp-comp-item-remove js-remove-item" data-id="${item.id}" title="Quitar">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        
                        <div class="mp-comp-item-title">${item.title}</div>
                        
                        <div class="mp-comp-item-body">
                            <img src="${item.img}" alt="prod">
                            <div class="mp-comp-item-prices">
                                ${item.price}
                                <span class="mp-comp-item-lbl">Transferencia / Débito</span>
                            </div>
                        </div>
                    </div>
                `;
                slotsContainer.insertAdjacentHTML('beforeend', html);
            });

            document.querySelectorAll('.js-compare-cb').forEach(cb => {
                cb.checked = compareList.some(i => i.id === cb.dataset.id);
            });
        }

        document.body.addEventListener('change', function(e) {
            if(e.target.classList.contains('js-compare-cb')) {
                const cb = e.target;
                const prodData = { id: cb.dataset.id, img: cb.dataset.img, title: cb.dataset.title, price: cb.dataset.price };

                if(cb.checked) {
                    if(compareList.length >= maxCompare) {
                        showNotif('error', `Solo puedes comparar hasta ${maxCompare} productos.`);
                        cb.checked = false; 
                        return;
                    }
                    compareList.push(prodData);
                } else {
                    compareList = compareList.filter(i => i.id !== prodData.id);
                }
                updateCompareUI();
            }
        });

        slotsContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.js-remove-item');
            if(removeBtn) {
                const idToRemove = removeBtn.dataset.id;
                compareList = compareList.filter(i => i.id !== idToRemove);
                updateCompareUI();
            }
        });

        document.getElementById('mp-comp-clear').addEventListener('click', function() {
            compareList = [];
            updateCompareUI();
            showNotif('success', 'Comparador vaciado.');
        });

        document.getElementById('mp-comp-close').addEventListener('click', function() {
            compareBar.classList.remove('show');
        });

        document.getElementById('mp-comp-btn').addEventListener('click', function() {
            if(compareList.length < 2) {
                showNotif('error', 'Selecciona al menos 2 productos para comparar.');
                return;
            }
            const ids = compareList.map(i => i.id).join(',');
            window.location.href = '/comparar/?ids=' + ids; 
        });

        updateCompareUI();
    });
    </script>
    <?php
}