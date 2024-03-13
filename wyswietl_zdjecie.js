document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('.clickable-image').forEach(image => {
        image.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            modal.style.zIndex = '1000';
            
            const img = document.createElement('img');
            img.src = this.src;
            img.style.maxWidth = '80%';
            img.style.maxHeight = '80%';
            
            modal.appendChild(img);
            
            modal.addEventListener('click', () => {
                modal.remove();
            });
            
            document.body.appendChild(modal);
        });
    });
});