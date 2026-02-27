

<?php $__env->startSection('content'); ?>


<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">💰 Revenus</h1>
        <p class="text-sm text-gray-500 mt-1">Analyse complète des revenus générés par la plateforme</p>
    </div>
    <a href="<?php echo e(route('admin.revenus.export', request()->query())); ?>"
       class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-all duration-300">
        📥 Exporter Excel
    </a>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

    <div class="bg-white rounded-xl p-5 shadow border-l-4 border-[#1DA1F2]">
        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Aujourd'hui</p>
        <p class="text-2xl font-bold text-[#1DA1F2] mt-1">
            <?php echo e(number_format($kpis['today'], 0, ',', ' ')); ?> XAF
        </p>
        <?php $diffDay = $kpis['yesterday'] > 0 ? (($kpis['today'] - $kpis['yesterday']) / $kpis['yesterday']) * 100 : 0; ?>
        <p class="text-xs mt-1 <?php echo e($diffDay >= 0 ? 'text-green-600' : 'text-red-500'); ?> font-semibold">
            <?php echo e($diffDay >= 0 ? '↑' : '↓'); ?> <?php echo e(number_format(abs($diffDay), 1)); ?>% vs hier
        </p>
    </div>

    <div class="bg-white rounded-xl p-5 shadow border-l-4 border-purple-500">
        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Cette semaine</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">
            <?php echo e(number_format($kpis['this_week'], 0, ',', ' ')); ?> XAF
        </p>
        <?php $diffWeek = $kpis['last_week_total'] > 0 ? (($kpis['this_week'] - $kpis['last_week_total']) / $kpis['last_week_total']) * 100 : 0; ?>
        <p class="text-xs mt-1 <?php echo e($diffWeek >= 0 ? 'text-green-600' : 'text-red-500'); ?> font-semibold">
            <?php echo e($diffWeek >= 0 ? '↑' : '↓'); ?> <?php echo e(number_format(abs($diffWeek), 1)); ?>% vs sem. dern.
        </p>
    </div>

    <div class="bg-white rounded-xl p-5 shadow border-l-4 border-green-500">
        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Ce mois</p>
        <p class="text-2xl font-bold text-green-600 mt-1">
            <?php echo e(number_format($kpis['this_month'], 0, ',', ' ')); ?> XAF
        </p>
        <?php $diffMonth = $kpis['last_month'] > 0 ? (($kpis['this_month'] - $kpis['last_month']) / $kpis['last_month']) * 100 : 0; ?>
        <p class="text-xs mt-1 <?php echo e($diffMonth >= 0 ? 'text-green-600' : 'text-red-500'); ?> font-semibold">
            <?php echo e($diffMonth >= 0 ? '↑' : '↓'); ?> <?php echo e(number_format(abs($diffMonth), 1)); ?>% vs mois dern.
        </p>
    </div>

    <div class="bg-white rounded-xl p-5 shadow border-l-4 border-yellow-500">
        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Cette année</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">
            <?php echo e(number_format($kpis['this_year'], 0, ',', ' ')); ?> XAF
        </p>
        <p class="text-xs mt-1 text-gray-400 font-semibold">
            Total all time : <?php echo e(number_format($kpis['total_all'], 0, ',', ' ')); ?> XAF
        </p>
    </div>

</div>


<div class="flex gap-2 mb-6">
    <?php $__currentLoopData = ['day' => 'Journalier', 'week' => 'Hebdomadaire', 'month' => 'Mensuel', 'year' => 'Annuel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.revenus.index', array_merge(request()->query(), ['period' => $key]))); ?>"
           class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all duration-200
           <?php echo e(request('period', 'month') === $key
               ? 'bg-[#1DA1F2] text-white border-[#1DA1F2]'
               : 'bg-white text-gray-500 border-gray-200 hover:border-[#1DA1F2] hover:text-[#1DA1F2]'); ?>">
            <?php echo e($label); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="bg-white rounded-xl shadow p-6 mb-6">
    <p class="text-sm font-bold text-gray-700 mb-4">🔍 Filtres de recherche</p>

    <form method="GET" action="<?php echo e(route('admin.revenus.index')); ?>">
        <input type="hidden" name="period" value="<?php echo e(request('period', 'month')); ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Date début</label>
                <input type="date" name="date_start" value="<?php echo e(request('date_start')); ?>"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Date fin</label>
                <input type="date" name="date_end" value="<?php echo e(request('date_end')); ?>"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Pays</label>
                <select name="country_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
                    <option value="">Tous les pays</option>
                    <?php $__currentLoopData = $filters['countries']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>" <?php echo e(request('country_id') == $country->id ? 'selected' : ''); ?>>
                            <?php echo e($country->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Ville</label>
                <select name="city_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
                    <option value="">Toutes les villes</option>
                    <?php $__currentLoopData = $filters['cities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city->id); ?>" <?php echo e(request('city_id') == $city->id ? 'selected' : ''); ?>>
                            <?php echo e($city->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Chauffeur</label>
                <select name="driver_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
                    <option value="">Tous les chauffeurs</option>
                    <?php $__currentLoopData = $filters['drivers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($driver->id); ?>" <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>>
                            <?php echo e($driver->user->name ?? '-'); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Client</label>
                <select name="user_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
                    <option value="">Tous les clients</option>
                    <?php $__currentLoopData = $filters['clients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($client->id); ?>" <?php echo e(request('user_id') == $client->id ? 'selected' : ''); ?>>
                            <?php echo e($client->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Montant min (XAF)</label>
                <input type="number" name="montant_min" value="<?php echo e(request('montant_min')); ?>"
                       placeholder="0"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
            </div>

            <div>
                <label class="text-xs text-gray-400 font-semibold uppercase block mb-1">Montant max (XAF)</label>
                <input type="number" name="montant_max" value="<?php echo e(request('montant_max')); ?>"
                       placeholder="999999"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#1DA1F2]">
            </div>

        </div>

        <div class="flex gap-3 mt-5">
            <button type="submit"
                    class="bg-[#1DA1F2] hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-300">
                ✅ Appliquer
            </button>
            <a href="<?php echo e(route('admin.revenus.index')); ?>"
               class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-300">
                🔄 Réinitialiser
            </a>
        </div>

    </form>
</div>


<div class="bg-white rounded-xl shadow overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b">
        <p class="font-bold text-gray-700">📋 Détail des transactions</p>
        <p class="text-sm text-gray-400">
            <?php echo e($data->total()); ?> résultat(s) —
            Total filtré : <span class="font-bold text-green-600"><?php echo e(number_format($total_query, 0, ',', ' ')); ?> XAF</span>
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-400 font-semibold">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Chauffeur</th>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-left">Pays</th>
                    <th class="px-4 py-3 text-left">Ville</th>
                    <th class="px-4 py-3 text-left">Montant course</th>
                    <th class="px-4 py-3 text-left">Taux</th>
                    <th class="px-4 py-3 text-left">Commission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400 text-xs">#<?php echo e($commission->id); ?></td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($commission->earned_at?->format('d/m/Y H:i')); ?>

                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">
                            <?php echo e($commission->driver?->user?->name ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($commission->user?->name ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($commission->country?->name ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($commission->city?->name ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-700 font-semibold">
                            <?php echo e(number_format($commission->montant_course, 0, ',', ' ')); ?> <?php echo e($commission->currency); ?>

                        </td>
                        <td class="px-4 py-3">
                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-semibold">
                                <?php echo e($commission->taux_applique); ?>%
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold text-green-600">
                            <?php echo e(number_format($commission->montant_commission, 0, ',', ' ')); ?> <?php echo e($commission->currency); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                            Aucune commission trouvée pour ces filtres.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($data->hasPages()): ?>
        <div class="px-6 py-4 border-t">
            <?php echo e($data->appends(request()->query())->links()); ?>

        </div>
    <?php endif; ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/revenus/index.blade.php ENDPATH**/ ?>