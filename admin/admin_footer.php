        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Скрипт для адаптивного переключения сайдбара на мобильных устройствах
        document.addEventListener('DOMContentLoaded', function() {
            const mediaQuery = window.matchMedia('(max-width: 768px)');
            
            function handleMediaChange(e) {
                const sidebar = document.querySelector('.admin-sidebar');
                const content = document.querySelector('.admin-content');
                
                if (e.matches) {
                    // Мобильная версия
                    sidebar.style.position = 'relative';
                    content.style.marginLeft = '0';
                } else {
                    // Десктопная версия
                    sidebar.style.position = 'fixed';
                    content.style.marginLeft = '280px';
                }
            }
            
            // Вызов функции при загрузке страницы
            handleMediaChange(mediaQuery);
            
            // Слушаем изменения размера экрана
            mediaQuery.addEventListener('change', handleMediaChange);
        });
    </script>
</body>
</html>
