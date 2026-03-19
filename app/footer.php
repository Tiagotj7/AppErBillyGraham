<?php
declare(strict_types=1);
?>
<?php if (!empty($_SESSION['user'])): ?>
</div>
<?php endif; ?>

<script src="/assets/script.js"></script>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/pwabuilder-sw.js')
        .then(function(registration) {
          console.log('Service Worker registrado:', registration.scope);
        })
        .catch(function(error) {
          console.warn('Falha ao registrar Service Worker:', error);
        });
    });
  }
</script>
</body>
</html>