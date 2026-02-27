

<?php $__env->startSection('content'); ?>

<div class="max-w-7xl mx-auto">

    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">📊 Gestion des Commissions</h2>
            <p class="text-gray-500 text-sm mt-1">Définissez les taux par pays, type de véhicule ou chauffeur</p>
        </div>
        <a href="<?php echo e(route('admin.commission-rates.export')); ?>"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            📥 Exporter CSV
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center gap-2">
                <span class="font-bold text-gray-700">⚙️ Règles actives</span>
            </div>

            
            <div class="px-5 py-2 bg-gray-50 border-b text-xs text-gray-500 flex flex-wrap gap-1 items-center">
                <span class="font-semibold">Priorité :</span>
                <span class="bg-blue-600 text-white px-2 py-0.5 rounded-full">Chauffeur</span>
                <span>›</span>
                <span class="bg-cyan-500 text-white px-2 py-0.5 rounded-full">Type véhicule</span>
                <span>›</span>
                <span class="bg-green-600 text-white px-2 py-0.5 rounded-full">Pays</span>
                <span>›</span>
                <span class="bg-gray-500 text-white px-2 py-0.5 rounded-full">Global</span>
            </div>

            
            <div class="divide-y">
                <?php $__empty_1 = true; $__currentLoopData = $allRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-5 py-3 flex justify-between items-center">
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if($rate->type === 'global'): ?>
                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">🌍 Global</span>
                        <?php elseif($rate->type === 'country'): ?>
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">🚩 <?php echo e($rate->country); ?></span>
                        <?php elseif($rate->type === 'vehicle_type'): ?>
                            <span class="bg-cyan-100 text-cyan-700 text-xs px-2 py-1 rounded-full">🚗 <?php echo e($rate->vehicle_type); ?></span>
                        <?php elseif($rate->type === 'driver'): ?>
                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">
                                👤 <?php echo e(optional($rate->driver)->first_name); ?> <?php echo e(optional($rate->driver)->last_name); ?>

                            </span>
                        <?php endif; ?>

                        <span class="font-bold text-gray-800"><?php echo e($rate->rate); ?>%</span>

                        <?php if($rate->description): ?>
                            <span class="text-gray-400 text-xs">— <?php echo e($rate->description); ?></span>
                        <?php endif; ?>

                        <?php if($rate->is_active): ?>
                            <span class="bg-green-100 text-green-600 text-xs px-2 py-0.5 rounded-full">✓ Actif</span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">✗ Inactif</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-1 ml-2 shrink-0">
                        <button class="btn-edit-rule hover:bg-blue-50 p-1.5 rounded-lg transition text-sm"
                            data-id="<?php echo e($rate->id); ?>"
                            data-type="<?php echo e($rate->type); ?>"
                            data-rate="<?php echo e($rate->rate); ?>"
                            data-description="<?php echo e($rate->description); ?>"
                            data-country="<?php echo e($rate->country); ?>"
                            data-vehicle="<?php echo e($rate->vehicle_type); ?>"
                            data-driver="<?php echo e($rate->driver_id); ?>"
                            title="Modifier">✏️</button>

                        <?php if($rate->type !== 'global'): ?>
                        <form action="<?php echo e(route('admin.commission-rates.destroy', $rate->id)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button onclick="return confirm('Supprimer cette règle ?')"
                                class="hover:bg-red-50 p-1.5 rounded-lg transition text-sm" title="Supprimer">🗑️</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-gray-400 py-10">
                    <p class="text-3xl mb-2">📭</p>
                    <p>Aucune règle définie</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b">
                <span class="font-bold text-gray-700" id="form-title">➕ Ajouter / Modifier une règle</span>
            </div>
            <div class="p-5">
                <form action="<?php echo e(route('admin.commission-rates.store')); ?>" method="POST" id="commission-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="form-method" value="POST">

                    
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Type de règle <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">

                            <label class="rule-card flex items-start gap-2 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" for="type_global">
                                <input type="radio" name="type" id="type_global" value="global" class="mt-0.5 accent-blue-600" checked onchange="switchType('global')">
                                <div>
                                    <p class="font-semibold text-sm">🌍 Global</p>
                                    <p class="text-xs text-gray-400">S'applique à tous</p>
                                </div>
                            </label>

                            <label class="rule-card flex items-start gap-2 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" for="type_country">
                                <input type="radio" name="type" id="type_country" value="country" class="mt-0.5 accent-blue-600" onchange="switchType('country')">
                                <div>
                                    <p class="font-semibold text-sm">🚩 Par pays</p>
                                    <p class="text-xs text-gray-400">Selon le pays</p>
                                </div>
                            </label>

                            <label class="rule-card flex items-start gap-2 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" for="type_vehicle">
                                <input type="radio" name="type" id="type_vehicle" value="vehicle_type" class="mt-0.5 accent-blue-600" onchange="switchType('vehicle_type')">
                                <div>
                                    <p class="font-semibold text-sm">🚗 Par véhicule</p>
                                    <p class="text-xs text-gray-400">Selon le type</p>
                                </div>
                            </label>

                            <label class="rule-card flex items-start gap-2 p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" for="type_driver">
                                <input type="radio" name="type" id="type_driver" value="driver" class="mt-0.5 accent-blue-600" onchange="switchType('driver')">
                                <div>
                                    <p class="font-semibold text-sm">👤 Par chauffeur</p>
                                    <p class="text-xs text-gray-400">Contrat individuel</p>
                                </div>
                            </label>

                        </div>
                    </div>

                    
                    <div class="mb-4 hidden" id="field-country">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            🚩 Pays <span class="text-red-500">*</span>
                        </label>
                        <select name="country" id="select-country"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Sélectionner un pays --</option>
                            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($country); ?>"><?php echo e($country); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="mb-4 hidden" id="field-vehicle">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            🚗 Type de véhicule <span class="text-red-500">*</span>
                        </label>
                        <select name="vehicle_type" id="select-vehicle"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Sélectionner un type --</option>
                            <?php $__currentLoopData = ['Standard', 'Confort', 'Van', 'PMR']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vType); ?>"><?php echo e($vType); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['vehicle_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="mb-4 hidden" id="field-driver">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            👤 Chauffeur <span class="text-red-500">*</span>
                        </label>
                        <select name="driver_id" id="select-driver"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Rechercher un chauffeur --</option>
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($driver->id); ?>">
                                    <?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?>

                                    <?php if($driver->phone): ?> — <?php echo e($driver->phone); ?> <?php endif; ?>
                                    <?php if(isset($driver->vehicle_country)): ?> (<?php echo e($driver->vehicle_country); ?>) <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['driver_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Taux (%) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex">
                                <input type="number" name="rate" id="input-rate"
                                    class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Ex: 15" min="0" max="100" step="0.01" required>
                                <span class="bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg px-3 py-2 text-sm text-gray-500">%</span>
                            </div>
                            <?php $__errorArgs = ['rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                    class="w-4 h-4 accent-blue-600">
                                <span class="text-sm text-gray-600">Actif</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" id="input-description"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Congo Brazzaville, SUV premium...">
                    </div>

                    
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-[#1DA1F2] hover:bg-blue-700 text-white py-2 rounded-lg font-semibold text-sm transition">
                            💾 <span id="btn-label">Enregistrer la règle</span>
                        </button>
                        <button type="button" onclick="resetForm()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                            ✕ Reset
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow mt-6 p-5">
        <form method="GET" action="<?php echo e(route('admin.commission-rates.index')); ?>">
            <div class="flex flex-wrap gap-2 mb-4">
                <?php $__currentLoopData = ['day' => "Aujourd'hui", 'week' => 'Cette semaine', 'month' => 'Ce mois', 'year' => 'Cette année']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="?period=<?php echo e($key); ?>"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                   <?php echo e($period === $key ? 'bg-[#1DA1F2] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'); ?>">
                    <?php echo e($label); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Chauffeur</label>
                    <select name="driver_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($driver->id); ?>" <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>>
                                <?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Pays</label>
                    <select name="country" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($country); ?>" <?php echo e(request('country') === $country ? 'selected' : ''); ?>><?php echo e($country); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Ville</label>
                    <select name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($city); ?>" <?php echo e(request('city') === $city ? 'selected' : ''); ?>><?php echo e($city); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Type véhicule</label>
                    <select name="vehicle_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = ['Standard', 'Confort', 'Van', 'PMR']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($vType); ?>" <?php echo e(request('vehicle_type') === $vType ? 'selected' : ''); ?>><?php echo e($vType); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button type="submit"
                    class="bg-[#1DA1F2] hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    🔍 Filtrer
                </button>
                <a href="<?php echo e(route('admin.commission-rates.index')); ?>"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                    ✕ Reset
                </a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow mt-4 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#Trip</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Chauffeur</th>
                        <th class="px-4 py-3 text-left">Client</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                        <th class="px-4 py-3 text-right">Net chauffeur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-gray-500">#<?php echo e($trip->id); ?></td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e(\Carbon\Carbon::parse($trip->completed_at)->format('d/m/Y H:i')); ?>

                        </td>
                        <td class="px-4 py-3 font-medium">
                            <?php echo e(optional($trip->driver)->first_name); ?> <?php echo e(optional($trip->driver)->last_name); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php if($trip->user): ?>
                                <?php echo e($trip->user->name ?? ($trip->user->first_name . ' ' . $trip->user->last_name)); ?>

                            <?php else: ?>
                                <span class="text-gray-300">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">
                            <?php echo e(number_format($trip->amount, 0, ',', ' ')); ?> FCFA
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-red-500">
                            <?php echo e(number_format($trip->commission, 0, ',', ' ')); ?> FCFA
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">
                            <?php echo e(number_format($trip->driver_net, 0, ',', ' ')); ?> FCFA
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-10">
                            Aucun trajet pour cette période
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($trips->hasPages()): ?>
        <div class="px-4 py-3 border-t">
            <?php echo e($trips->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>

<style>
.rule-card:has(input:checked) {
    border-color: #1DA1F2;
    background-color: #eff8ff;
}
</style>

<?php $__env->startPush('scripts'); ?>
<script>
function switchType(type) {
    const fields = {
        country:      document.getElementById('field-country'),
        vehicle_type: document.getElementById('field-vehicle'),
        driver:       document.getElementById('field-driver'),
    };

    Object.entries(fields).forEach(([key, el]) => {
        el.classList.add('hidden');
        const sel = el.querySelector('select');
        if (sel) sel.removeAttribute('required');
    });

    if (type !== 'global' && fields[type]) {
        fields[type].classList.remove('hidden');
        const sel = fields[type].querySelector('select');
        if (sel) sel.setAttribute('required', 'required');
    }
}

document.querySelectorAll('.btn-edit-rule').forEach(btn => {
    btn.addEventListener('click', function () {
        const id      = this.dataset.id;
        const type    = this.dataset.type;
        const rate    = this.dataset.rate;
        const desc    = this.dataset.description;
        const country = this.dataset.country;
        const vehicle = this.dataset.vehicle;
        const driver  = this.dataset.driver;

        const form = document.getElementById('commission-form');
        form.action = `/admin/commission-rates/${id}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('form-title').textContent = '✏️ Modifier la règle';
        document.getElementById('btn-label').textContent  = 'Mettre à jour';

        const radioMap = {
            global:       'type_global',
            country:      'type_country',
            vehicle_type: 'type_vehicle',
            driver:       'type_driver',
        };
        if (radioMap[type]) document.getElementById(radioMap[type]).checked = true;
        switchType(type);

        document.getElementById('input-rate').value        = rate;
        document.getElementById('input-description').value = desc ?? '';

        if (type === 'country')      document.getElementById('select-country').value = country;
        if (type === 'vehicle_type') document.getElementById('select-vehicle').value = vehicle;
        if (type === 'driver')       document.getElementById('select-driver').value  = driver;

        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

function resetForm() {
    const form = document.getElementById('commission-form');
    form.reset();
    form.action = "<?php echo e(route('admin.commission-rates.store')); ?>";
    document.getElementById('form-method').value  = 'POST';
    document.getElementById('form-title').textContent = '➕ Ajouter / Modifier une règle';
    document.getElementById('btn-label').textContent  = 'Enregistrer la règle';
    switchType('global');
}

switchType('global');
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/commissions/index.blade.php ENDPATH**/ ?>