// Отладочный скрипт для проверки работы toggle-sidebar
document.addEventListener('DOMContentLoaded', function() {
    console.log('debug-toggle.js загружен');
    
    // Проверяем наличие элементов
    const toggleButton = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    console.log('Найденные элементы:', {
        toggleButton: toggleButton,
        sidebar: sidebar,
        mainContent: mainContent
    });
    
    // Проверяем стили
    if (toggleButton && sidebar && mainContent) {
        const toggleStyles = window.getComputedStyle(toggleButton);
        const sidebarStyles = window.getComputedStyle(sidebar);
        const mainContentStyles = window.getComputedStyle(mainContent);
        
        console.log('Стили toggle-button:', {
            position: toggleStyles.position,
            zIndex: toggleStyles.zIndex,
            display: toggleStyles.display,
            visibility: toggleStyles.visibility,
            opacity: toggleStyles.opacity
        });
        
        console.log('Стили sidebar:', {
            position: sidebarStyles.position,
            zIndex: sidebarStyles.zIndex,
            left: sidebarStyles.left,
            width: sidebarStyles.width
        });
        
        console.log('Стили main-content:', {
            marginLeft: mainContentStyles.marginLeft
        });
        
        // Добавляем свой обработчик для кнопки
        toggleButton.addEventListener('click', function(e) {
            console.log('debug-toggle: Кнопка нажата');
            console.log('Текущие классы sidebar:', sidebar.className);
            console.log('Текущие классы main-content:', mainContent.className);
            
            // Принудительно переключаем классы
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                console.log('debug-toggle: Добавлены классы collapsed и expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                console.log('debug-toggle: Удалены классы collapsed и expanded');
            }
        }, true);
    }
}); 