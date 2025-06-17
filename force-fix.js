// Принудительное исправление проблемы с toggle-sidebar
(function() {
    // Функция для создания новой кнопки toggle
    function createNewToggleButton() {
        // Удаляем старую кнопку, если она есть
        const oldButton = document.querySelector('.toggle-sidebar');
        if (oldButton) {
            oldButton.remove();
        }
        
        // Создаем новую кнопку
        const newButton = document.createElement('button');
        newButton.className = 'toggle-sidebar';
        newButton.innerHTML = '<i class="fas fa-bars"></i>';
        newButton.style.position = 'fixed';
        newButton.style.top = '15px';
        newButton.style.left = '15px';
        newButton.style.zIndex = '10000';
        newButton.style.width = '40px';
        newButton.style.height = '40px';
        newButton.style.borderRadius = '50%';
        newButton.style.background = '#ff0000';
        newButton.style.color = 'white';
        newButton.style.border = 'none';
        newButton.style.display = 'flex';
        newButton.style.alignItems = 'center';
        newButton.style.justifyContent = 'center';
        newButton.style.cursor = 'pointer';
        
        // Добавляем обработчик события
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Новая кнопка toggle нажата');
            
            // Переключаем класс на body
            document.body.classList.toggle('sidebar-collapsed');
            
            // Сохраняем состояние в localStorage
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
            
            // Применяем стили напрямую
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (document.body.classList.contains('sidebar-collapsed')) {
                if (sidebar) sidebar.style.left = '-200px';
                if (mainContent) mainContent.style.marginLeft = '50px';
            } else {
                if (sidebar) sidebar.style.left = '0';
                if (mainContent) mainContent.style.marginLeft = '250px';
            }
        });
        
        // Добавляем кнопку в body
        document.body.appendChild(newButton);
        return newButton;
    }
    
    // Функция для применения сохраненного состояния
    function applySavedState() {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar) sidebar.style.left = '-200px';
            if (mainContent) mainContent.style.marginLeft = '50px';
        }
    }
    
    // Запускаем после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            createNewToggleButton();
            applySavedState();
        });
    } else {
        createNewToggleButton();
        applySavedState();
    }
})(); 