

<?php $__env->startSection('content'); ?>

<h1 class="text-2xl font-bold mb-6">Créer un Chauffeur</h1>

<div class="bg-white p-6 rounded-lg shadow">

<?php if($errors->any()): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        Veuillez corriger les erreurs ci-dessous.
    </div>
<?php endif; ?>

<form method="POST"
      action="<?php echo e(route('admin.drivers.store')); ?>"
      enctype="multipart/form-data">

    <?php echo csrf_field(); ?>

    <!-- ========================= -->
    <!-- INFORMATIONS PERSONNELLES -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Informations personnelles</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        <?php echo $__env->make('admin.components.input', ['name' => 'first_name', 'label' => 'Prénom'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'last_name', 'label' => 'Nom'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'date_of_birth', 'label' => 'Date de naissance', 'type' => 'date'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'place_of_birth', 'label' => 'Lieu de naissance'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'phone', 'label' => 'Téléphone'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'password', 'label' => 'Mot de passe', 'type' => 'password'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'country', 'label' => 'Pays de résidence'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.image-input', ['name' => 'avatar', 'label' => 'Photo du chauffeur'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

    <!-- ========================= -->
    <!-- INFORMATIONS VEHICULE -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Informations du véhicule</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        <?php echo $__env->make('admin.components.input', ['name' => 'vehicle_brand', 'label' => 'Marque'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'vehicle_model', 'label' => 'Modèle'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'vehicle_color', 'label' => 'Couleur du véhicule'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'seats_available', 'label' => 'Nombre de places', 'type' => 'number'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'vehicle_plate_number', 'label' => 'Immatriculation'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.image-input', ['name' => 'vehicle_registration_image', 'label' => 'Carte grise'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.image-input', ['name' => 'vehicle_insurance_image', 'label' => 'Assurance véhicule'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

    <!-- ========================= -->
    <!-- PERMIS -->
    <!-- ========================= -->
    <h2 class="text-lg font-semibold mb-4">Permis de conduire</h2>

    <div class="grid grid-cols-2 gap-4 mb-6">

        <?php echo $__env->make('admin.components.input', ['name' => 'license_number', 'label' => 'Numéro du permis'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.input', ['name' => 'license_issue_date', 'label' => 'Date d’émission', 'type' => 'date'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.input', ['name' => 'license_expiry', 'label' => 'Date d’expiration', 'type' => 'date'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.components.image-input', ['name' => 'license_image_recto', 'label' => 'Permis recto'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.components.image-input', ['name' => 'license_image_verso', 'label' => 'Permis verso'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
        Enregistrer le Chauffeur
    </button>

</form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/drivers/create.blade.php ENDPATH**/ ?>