

<?php $__env->startSection('content'); ?>

<h1 class="text-2xl font-bold mb-6">
    🚗 Détails Chauffeur
</h1>

<div class="grid grid-cols-2 gap-6">

    <!-- INFO PERSONNELLE -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-bold mb-4">Informations personnelles</h2>

        <p><strong>Nom :</strong> <?php echo e($driver->user->first_name); ?> <?php echo e($driver->user->last_name); ?></p>
        <p><strong>Téléphone :</strong> <?php echo e($driver->user->phone); ?></p>
        <p><strong>Email :</strong> <?php echo e($driver->user->email); ?></p>
        <p><strong>Statut :</strong>
            <?php echo e($driver->user->is_active ? 'Actif' : 'Désactivé'); ?>

        </p>

        <?php if($driver->user->avatar): ?>
            <div class="mt-4">
                <p class="font-semibold mb-2">Photo profil</p>
                <img src="<?php echo e(asset('storage/'.$driver->user->avatar)); ?>"
                     class="w-40 rounded shadow">
            </div>
        <?php endif; ?>
    </div>


    <!-- INFO VEHICULE -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-bold mb-4">Véhicule</h2>

        <p><strong>Marque :</strong> <?php echo e($driver->vehicle_brand); ?></p>
        <p><strong>Modèle :</strong> <?php echo e($driver->vehicle_model); ?></p>
        <p><strong>Année :</strong> <?php echo e($driver->vehicle_year); ?></p>
        <p><strong>Couleur :</strong> <?php echo e($driver->vehicle_color); ?></p>
        <p><strong>Plaque :</strong> <?php echo e($driver->vehicle_plate_number); ?></p>

        <?php if($driver->vehicle_registration_image): ?>
            <div class="mt-4">
                <p class="font-semibold mb-2">Carte grise</p>
                <img src="<?php echo e(asset('storage/'.$driver->vehicle_registration_image)); ?>"
                     class="w-48 rounded shadow">
            </div>
        <?php endif; ?>

        <?php if($driver->vehicle_insurance_image): ?>
            <div class="mt-4">
                <p class="font-semibold mb-2">Assurance</p>
                <img src="<?php echo e(asset('storage/'.$driver->vehicle_insurance_image)); ?>"
                     class="w-48 rounded shadow">
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- DOCUMENTS -->
<div class="bg-white p-6 rounded shadow mt-6">
    <h2 class="font-bold mb-4">Documents chauffeur</h2>

    <div class="grid grid-cols-3 gap-6">

        <?php if($driver->license_image): ?>
        <div>
            <p class="font-semibold mb-2">Permis de conduire</p>
            <img src="<?php echo e(asset('storage/'.$driver->license_image)); ?>"
                 class="rounded shadow">
        </div>
        <?php endif; ?>

        <?php if($driver->id_card_image): ?>
        <div>
            <p class="font-semibold mb-2">Carte d'identité</p>
            <img src="<?php echo e(asset('storage/'.$driver->id_card_image)); ?>"
                 class="rounded shadow">
        </div>
        <?php endif; ?>

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/drivers/show.blade.php ENDPATH**/ ?>