    <footer class="mt-auto py-6 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                Created by <a href="https://github.com/arielthekillers" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">arielthekillers</a>
            </p>
        </div>
    </footer>

    <script>
        function initTomSelects() {
            document.querySelectorAll('select.ts-control, select.tom-select').forEach((el) => {
                if (el.tomselect) return; 
                new TomSelect(el, {create: false, dropdownParent: 'body'});
            });
        }
        document.addEventListener('DOMContentLoaded', initTomSelects);

        // -- User dropdown ----------------------------------------------
        function toggleUserDropdown() {
            const panel   = document.getElementById('user-dropdown-panel');
            const chevron = document.getElementById('user-chevron');
            if (!panel) return;
            const isOpen = !panel.classList.contains('hidden');
            panel.classList.toggle('hidden');
            if (chevron) chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('user-dropdown-wrapper');
            const panel   = document.getElementById('user-dropdown-panel');
            if (wrapper && panel && !wrapper.contains(e.target)) {
                panel.classList.add('hidden');
                const chevron = document.getElementById('user-chevron');
                if (chevron) chevron.style.transform = '';
            }
        });
    </script>
</body>
</html>
