// Функция для инициализации сайдбара
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация sidebar.js');
    
    const toggleButton = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const pageWrapper = document.querySelector('.page-wrapper');
    
    console.log('Элементы:', {
        toggleButton: toggleButton,
        sidebar: sidebar,
        mainContent: mainContent,
        pageWrapper: pageWrapper
    });
    
    if (!toggleButton || !sidebar || !mainContent) {
        console.error('Не найдены необходимые элементы для работы сайдбара');
        return;
    }
    
    // Проверяем, сохранено ли состояние сайдбара в localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    console.log('Сохраненное состояние сайдбара:', sidebarCollapsed);
    
    // Применяем сохраненное состояние
    if (sidebarCollapsed) {
        document.body.classList.add('sidebar-collapsed');
        console.log('Применено состояние collapsed к body');
    }
    
    // Обработчик клика по кнопке переключения
    toggleButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Toggle button clicked');
        
        // Переключаем класс на body вместо sidebar
        document.body.classList.toggle('sidebar-collapsed');
        
        console.log('Новое состояние сайдбара:', {
            collapsed: document.body.classList.contains('sidebar-collapsed')
        });
        
        // Сохраняем состояние в localStorage
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
    });
    
    // Обработка клика на пункты меню в мобильной версии
    const menuItems = document.querySelectorAll('.sidebar .link-item a');
    if (window.innerWidth <= 768) {
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (document.body.classList.contains('sidebar-collapsed')) {
                    return;
                }
                document.body.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'true');
            });
        });
    }
    
    // Адаптивное поведение при изменении размера окна
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    });
}); 