

<?php $__env->startSection('content'); ?>

<div class="flex h-[80vh] bg-white rounded shadow">

<!-- LISTE CONVERSATIONS -->

<div class="w-1/3 border-r overflow-y-auto">

<h2 class="text-xl font-bold p-4 border-b">
Conversations
</h2>

<?php $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<a href="<?php echo e(route('admin.messages.show',$trip->id)); ?>"

class="block p-4 border-b hover:bg-gray-100">

<div class="font-bold">

<?php echo e($trip->user->first_name); ?>


→

<?php echo e($trip->driver->first_name); ?>


</div>

<div class="text-sm text-gray-500">

Trip #<?php echo e($trip->id); ?>


</div>

</a>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>


<!-- ZONE MESSAGES -->

<div class="flex-1 flex items-center justify-center text-gray-400">

Sélectionnez une conversation

</div>


</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views\admin\messages\index.blade.php ENDPATH**/ ?>