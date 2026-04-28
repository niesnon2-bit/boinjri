<?php
declare(strict_types=1);
$title = isset($authFlowTitle) ? (string) $authFlowTitle : '';
?>
<header class="w-full bg-white border-b border-gray-200">
  <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
    <a href="<?php echo h(url('index.php')); ?>" class="font-bold text-[#4b3447]"><?php echo h(APP_NAME); ?></a>
    <?php if ($title !== ''): ?>
      <span class="text-sm text-gray-600"><?php echo h($title); ?></span>
    <?php endif; ?>
  </div>
</header>
