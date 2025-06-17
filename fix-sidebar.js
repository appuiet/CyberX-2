// Скрипт для исправления проблемы с toggle-sidebar
document.addEventListener('DOMContentLoaded', function() {
    console.log('fix-sidebar.js загружен');
    
    // Находим кнопку toggle-sidebar
    const toggleButton = document.querySelector('.toggle-sidebar');
    if (!toggleButton) {
        console.error('Кнопка toggle-sidebar не найдена');
        return;
    }
    
    // Удаляем все существующие обработчики событий
    const newToggleButton = toggleButton.cloneNode(true);
    toggleButton.parentNode.replaceChild(newToggleButton, toggleButton);
    
    // Добавляем новый обработчик
    newToggleButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Кнопка toggle-sidebar нажата (fix-sidebar.js)');
        
        // Переключаем класс на body
        document.body.classList.toggle('sidebar-collapsed');
        
        // Сохраняем состояние в localStorage
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
        
        console.log('Новое состояние сайдбара:', document.body.classList.contains('sidebar-collapsed'));
    });
    
    // Проверяем сохраненное состояние
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        document.body.classList.add('sidebar-collapsed');
        console.log('Применено сохраненное состояние sidebar-collapsed');
    }
}); 