<div class="col-span-2">
    <label class="block text-sm mb-1"><?php echo e($label); ?></label>

    <input type="file"
           name="<?php echo e($name); ?>"
           accept="image/*"
           onchange="previewImage(event, 'preview_<?php echo e($name); ?>')"
           class="border p-2 rounded w-full
           <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">

    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    <img id="preview_<?php echo e($name); ?>"
         class="mt-3 w-32 h-32 object-cover rounded hidden">
</div><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/components/image-input.blade.php ENDPATH**/ ?>