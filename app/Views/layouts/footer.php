    <footer class="mt-12 py-6 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                Created by <a href="https://github.com/arielthekillers" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-medium">arielthekillers</a>
            </p>
        </div>
    </footer>

    <script>
        function initTomSelects() {
            document.querySelectorAll('select.tom-select').forEach((el) => {
                if (el.tomselect) return; // Already initialized
                new TomSelect(el, {create: false, dropdownParent: 'body'});
            });
        }
        document.addEventListener('DOMContentLoaded', initTomSelects);
    </script>
    </body>
    </html>
