

<?php $__env->startSection('content'); ?>

<div class="flex h-[80vh] bg-white rounded shadow">

<!-- LISTE CONVERSATIONS -->

<div class="w-1/3 border-r overflow-y-auto">

<h2 class="text-xl font-bold p-4 border-b">
Conversations
</h2>

<?php $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<a href="<?php echo e(route('admin.messages.show',$t->id)); ?>"

class="block p-4 border-b hover:bg-gray-100
<?php if($trip->id==$t->id): ?> bg-gray-200 <?php endif; ?>
">

<div class="font-bold">

<?php echo e($t->user->first_name ?? 'User supprimé'); ?>


→

<?php echo e($t->driver->first_name ?? 'Driver supprimé'); ?>


</div>

<div class="text-sm text-gray-500">

Trip #<?php echo e($t->id); ?>


</div>

</a>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>



<!-- MESSAGES -->

<div class="flex-1 flex flex-col">

<!-- HEADER -->

<div class="p-4 border-b font-bold">

<?php echo e($trip->user->first_name ?? ''); ?>


↔

<?php echo e($trip->driver->first_name ?? ''); ?>


| Trip #<?php echo e($trip->id); ?>


</div>



<!-- LISTE MESSAGES -->

<div class="flex-1 overflow-y-auto p-4" id="messagesBox">

<?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<div class="mb-4">

<?php if(str_contains($message->sender_type,'User')): ?>

<div class="text-left">

<div class="inline-block bg-gray-200 p-3 rounded">

<?php echo e($message->content); ?>


</div>

</div>

<?php else: ?>

<div class="text-right">

<div class="inline-block bg-blue-500 text-white p-3 rounded">

<?php echo e($message->content); ?>


</div>

</div>

<?php endif; ?>

</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>

</div>

</div>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('scripts'); ?>

<script>

// Scroll automatique en bas

let box = document.getElementById("messagesBox");

box.scrollTop = box.scrollHeight;


// Refresh toutes les 5 secondes

setInterval(function(){

location.reload();

},5000);

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views\admin\messages\show.blade.php ENDPATH**/ ?>