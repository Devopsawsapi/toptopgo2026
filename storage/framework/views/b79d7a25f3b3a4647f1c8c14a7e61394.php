

<?php $__env->startSection('content'); ?>

<!-- MODAL ZOOM -->
<div id="zoom-modal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl w-full">
        <button onclick="closeZoom()" class="absolute -top-10 right-0 text-white text-3xl font-bold hover:text-[#FFC107]">✕</button>
        <img id="zoom-img" src="" class="w-full max-h-[85vh] object-contain rounded-xl shadow-2xl">
        <a id="zoom-download" href="" download
           class="mt-4 flex items-center justify-center gap-2 bg-[#1DA1F2] text-white py-2 px-6 rounded-xl font-semibold hover:bg-[#FFC107] hover:text-black transition-all duration-300">
            ⬇️ Télécharger
        </a>
    </div>
</div>

<?php
$countriesVilles = [
    'République du Congo' => ['Brazzaville','Pointe-Noire','Dolisie','Nkayi','Impfondo','Ouesso','Owando','Makoua','Sibiti','Mossendjo','Kindamba','Kinkala','Madingou','Loutété','Gamboma'],
    'Cameroun' => ['Yaoundé','Douala','Garoua','Bamenda','Bafoussam','Ngaoundéré','Bertoua','Maroua','Kumba','Nkongsamba','Edéa','Kribi','Ebolowa','Limbé','Buea'],
    'République Centrafricaine' => ['Bangui','Bimbo','Berbérati','Carnot','Bambari','Bouar','Bossangoa','Bria','Kaga-Bandoro','Mbaïki'],
    'Tchad' => ["N'Djamena",'Moundou','Sarh','Abéché','Kélo','Koumra','Pala','Am Timan','Bongor','Doba'],
    'Guinée Équatoriale' => ['Malabo','Bata','Ebebiyín','Aconibe','Añisoc','Luba','Evinayong','Mongomo','Mbini','Riaba'],
    'Gabon' => ['Libreville','Port-Gentil','Franceville','Oyem','Moanda','Mouila','Lambaréné','Tchibanga','Koulamoutou','Makokou','Bitam','Gamba','Ndjolé','Mitzic','Booué'],
    'Autre' => [],
];
?>

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.drivers.show', $driver->id)); ?>" class="text-gray-400 hover:text-gray-700 transition text-2xl">←</a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">✏️ Modifier le Chauffeur</h1>
                <p class="text-gray-500 text-sm mt-1"><?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?></p>
            </div>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.drivers.update', $driver->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- INFORMATIONS PERSONNELLES -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">👤 Informations Personnelles</h2>

            <!-- Photo de profil -->
            <div class="flex items-center gap-6 mb-6">
                <?php if($driver->profile_photo): ?>
                    <img src="<?php echo e(asset('storage/' . $driver->profile_photo)); ?>"
                         id="preview_profile_photo"
                         class="w-20 h-20 rounded-full object-cover border-4 border-[#1DA1F2] cursor-pointer"
                         onclick="openZoom('<?php echo e(asset('storage/' . $driver->profile_photo)); ?>')">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-full bg-[#1DA1F2] flex items-center justify-center text-3xl font-bold text-white">
                        <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                    </div>
                    <img id="preview_profile_photo" class="w-20 h-20 rounded-full object-cover border-4 border-[#1DA1F2] hidden">
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Photo de profil</label>
                    <input type="file" name="profile_photo" accept="image/*"
                           onchange="previewImage(event, 'preview_profile_photo')"
                           class="text-sm text-gray-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="<?php echo e(old('first_name', $driver->first_name)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]" required>
                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="<?php echo e(old('last_name', $driver->last_name)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]" required>
                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Téléphone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="<?php echo e(old('phone', $driver->phone)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]" required>
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Date de naissance</label>
                    <input type="date" name="birth_date" value="<?php echo e(old('birth_date', $driver->birth_date)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Lieu de naissance</label>
                    <input type="text" name="birth_place" value="<?php echo e(old('birth_place', $driver->birth_place)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Pays de naissance</label>
                    <input type="text" name="country_birth" value="<?php echo e(old('country_birth', $driver->country_birth)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
            </div>
        </div>

        <!-- PIÈCE D'IDENTITÉ -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">🪪 Pièce d'Identité</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Type de pièce</label>
                    <select name="id_card_type" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                        <option value="">-- Choisir --</option>
                        <?php $__currentLoopData = ["Carte d'Identité Nationale", "Passeport", "Carte de Résidence", "Carte Consulaire"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php echo e(old('id_card_type', $driver->id_card_type ?? '') == $type ? 'selected' : ''); ?>><?php echo e($type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Ville d'émission</label>
                    <input type="text" name="id_card_issue_city" value="<?php echo e(old('id_card_issue_city', $driver->id_card_issue_city)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Pays d'émission</label>
                    <input type="text" name="id_card_issue_country" value="<?php echo e(old('id_card_issue_country', $driver->id_card_issue_country)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Date d'émission</label>
                    <input type="date" name="id_card_issue_date" value="<?php echo e(old('id_card_issue_date', $driver->id_card_issue_date)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Date d'expiration</label>
                    <input type="date" name="id_card_expiry_date" value="<?php echo e(old('id_card_expiry_date', $driver->id_card_expiry_date)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
            </div>
        </div>

        <!-- VÉHICULE -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">🚗 Véhicule</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Plaque d'immatriculation</label>
                    <input type="text" name="vehicle_plate" value="<?php echo e(old('vehicle_plate', $driver->vehicle_plate)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                    <?php $__errorArgs = ['vehicle_plate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Marque</label>
                    <input type="text" name="vehicle_brand" value="<?php echo e(old('vehicle_brand', $driver->vehicle_brand)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Modèle</label>
                    <input type="text" name="vehicle_model" value="<?php echo e(old('vehicle_model', $driver->vehicle_model)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Type</label>
                    <select name="vehicle_type" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                        <option value="">-- Choisir --</option>
                        <?php $__currentLoopData = ['Berline', 'SUV', 'Van', 'Moto', 'Tricycle', 'Autre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php echo e(old('vehicle_type', $driver->vehicle_type) == $type ? 'selected' : ''); ?>><?php echo e($type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Couleur</label>
                    <input type="text" name="vehicle_color" value="<?php echo e(old('vehicle_color', $driver->vehicle_color)); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
                <!-- PAYS -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Pays</label>
                    <select name="vehicle_country" id="vehicle_country"
                            onchange="updateVilles(this.value)"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                        <option value="">-- Choisir un pays --</option>
                        <?php $__currentLoopData = array_keys($countriesVilles); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pays): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pays); ?>" <?php echo e(old('vehicle_country', $driver->vehicle_country) == $pays ? 'selected' : ''); ?>>
                                <?php echo e($pays); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <!-- VILLE -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Ville</label>
                    <select name="vehicle_city" id="vehicle_city"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                        <option value="">-- Choisir une ville --</option>
                        <?php $__currentLoopData = $countriesVilles[old('vehicle_country', $driver->vehicle_country)] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ville): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ville); ?>" <?php echo e(old('vehicle_city', $driver->vehicle_city) == $ville ? 'selected' : ''); ?>>
                                <?php echo e($ville); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <!-- Champ libre si Autre -->
                    <input type="text" name="vehicle_city_autre" id="vehicle_city_autre"
                           placeholder="Saisir la ville"
                           value="<?php echo e(old('vehicle_country', $driver->vehicle_country) == 'Autre' ? old('vehicle_city', $driver->vehicle_city) : ''); ?>"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 mt-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2] <?php echo e(old('vehicle_country', $driver->vehicle_country) == 'Autre' ? '' : 'hidden'); ?>">
                </div>
            </div>
        </div>

        <!-- DOCUMENTS KYC -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">📄 Documents KYC</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $docs = [
                    ['label' => '🪪 CNI Recto',    'name' => 'id_card_front'],
                    ['label' => '🪪 CNI Verso',     'name' => 'id_card_back'],
                    ['label' => '📋 Permis Recto',  'name' => 'license_front'],
                    ['label' => '📋 Permis Verso',  'name' => 'license_back'],
                    ['label' => '🚗 Carte grise',   'name' => 'vehicle_registration'],
                    ['label' => '🛡 Assurance',     'name' => 'insurance'],
                ];
                ?>

                <?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-semibold text-gray-700"><?php echo e($doc['label']); ?></p>
                    </div>
                    <div class="p-3">
                        <?php if($driver->{$doc['name']}): ?>
                            <?php $ext = pathinfo($driver->{$doc['name']}, PATHINFO_EXTENSION); ?>
                            <?php if(in_array(strtolower($ext), ['jpg','jpeg','png','webp'])): ?>
                                <div class="relative group">
                                    <img src="<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>"
                                         id="preview_<?php echo e($doc['name']); ?>"
                                         class="w-full h-28 object-cover rounded-lg mb-2 cursor-zoom-in group-hover:opacity-90 transition"
                                         onclick="openZoom('<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>')">
                                    <div class="absolute inset-0 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                        <button type="button"
                                                onclick="openZoom('<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>')"
                                                class="bg-black bg-opacity-60 text-white text-xs px-3 py-1 rounded-full">
                                            🔍 Zoom
                                        </button>
                                        <a href="<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>" download
                                           class="bg-black bg-opacity-60 text-white text-xs px-3 py-1 rounded-full">
                                            ⬇️ DL
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center gap-2 mb-2">
                                    <a href="<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>" target="_blank"
                                       class="text-[#1DA1F2] text-sm hover:underline">📎 Voir</a>
                                    <a href="<?php echo e(asset('storage/' . $driver->{$doc['name']})); ?>" download
                                       class="text-green-600 text-sm hover:underline">⬇️ Télécharger</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <img id="preview_<?php echo e($doc['name']); ?>" class="w-full h-28 object-cover rounded-lg mb-2 hidden cursor-zoom-in"
                                 onclick="openZoom(this.src)">
                            <div class="h-28 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs mb-2"
                                 id="placeholder_<?php echo e($doc['name']); ?>">
                                Non fourni
                            </div>
                        <?php endif; ?>
                        <input type="file" name="<?php echo e($doc['name']); ?>" accept="image/*,application/pdf"
                               onchange="previewImage(event, 'preview_<?php echo e($doc['name']); ?>')"
                               class="text-xs text-gray-500 w-full">
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- MOT DE PASSE -->
        <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">🔐 Mot de passe</h2>
            <p class="text-sm text-gray-400 mb-4">Laissez vide pour ne pas modifier le mot de passe.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nouveau mot de passe</label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#1DA1F2]">
                </div>
            </div>
        </div>

        <!-- BOUTONS -->
        <div class="flex gap-4 mb-10">
            <button type="submit"
                    class="flex-1 bg-[#1DA1F2] text-white py-3 rounded-xl font-semibold hover:bg-[#FFC107] hover:text-black transition-all duration-300">
                💾 Enregistrer les modifications
            </button>
            <a href="<?php echo e(route('admin.drivers.show', $driver->id)); ?>"
               class="flex-1 text-center bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all duration-300">
                Annuler
            </a>
        </div>

    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const countriesVilles = {
    "République du Congo": ["Brazzaville","Pointe-Noire","Dolisie","Nkayi","Impfondo","Ouesso","Owando","Makoua","Sibiti","Mossendjo","Kindamba","Kinkala","Madingou","Loutété","Gamboma"],
    "Cameroun": ["Yaoundé","Douala","Garoua","Bamenda","Bafoussam","Ngaoundéré","Bertoua","Maroua","Kumba","Nkongsamba","Edéa","Kribi","Ebolowa","Limbé","Buea"],
    "République Centrafricaine": ["Bangui","Bimbo","Berbérati","Carnot","Bambari","Bouar","Bossangoa","Bria","Kaga-Bandoro","Mbaïki"],
    "Tchad": ["N'Djamena","Moundou","Sarh","Abéché","Kélo","Koumra","Pala","Am Timan","Bongor","Doba"],
    "Guinée Équatoriale": ["Malabo","Bata","Ebebiyín","Aconibe","Añisoc","Luba","Evinayong","Mongomo","Mbini","Riaba"],
    "Gabon": ["Libreville","Port-Gentil","Franceville","Oyem","Moanda","Mouila","Lambaréné","Tchibanga","Koulamoutou","Makokou","Bitam","Gamba","Ndjolé","Mitzic","Booué"],
    "Autre": []
};

function updateVilles(pays) {
    const villeSelect = document.getElementById('vehicle_city');
    const villeAutre = document.getElementById('vehicle_city_autre');

    villeSelect.innerHTML = '<option value="">-- Choisir une ville --</option>';

    if (pays === 'Autre') {
        villeSelect.classList.add('hidden');
        villeAutre.classList.remove('hidden');
        villeAutre.name = 'vehicle_city';
    } else {
        villeSelect.classList.remove('hidden');
        villeAutre.classList.add('hidden');
        villeAutre.name = 'vehicle_city_autre';
        const villes = countriesVilles[pays] || [];
        villes.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            villeSelect.appendChild(opt);
        });
    }
}

function openZoom(src) {
    document.getElementById('zoom-img').src = src;
    document.getElementById('zoom-download').href = src;
    document.getElementById('zoom-modal').classList.remove('hidden');
    document.getElementById('zoom-modal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeZoom() {
    document.getElementById('zoom-modal').classList.add('hidden');
    document.getElementById('zoom-modal').classList.remove('flex');
    document.body.style.overflow = '';
}

document.getElementById('zoom-modal').addEventListener('click', function(e) {
    if (e.target === this) closeZoom();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeZoom();
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/drivers/edit.blade.php ENDPATH**/ ?>