<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$db = db();

$selectedMonth = trim((string) ($_GET['month'] ?? ''));
$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$searchMedicine = trim((string) ($_GET['q'] ?? ''));

$summaryRows = [];
$medicineRows = [];
$priceRows = [];
$monthOptions = [];
$categoryOptions = [];
$analysisReady = true;
$analysisError = '';
$monthColumn = 'report_month';

try {
    $monthColumnCheck = $db->query(
        "SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'vw_monthly_sales_summary'
           AND COLUMN_NAME IN ('report_month', 'year_month')
         LIMIT 1"
    )->fetchColumn();

    if (is_string($monthColumnCheck) && $monthColumnCheck !== '') {
        $monthColumn = $monthColumnCheck;
    }

    $monthOptions = $db->query(
        "SELECT DISTINCT {$monthColumn} AS report_month
         FROM vw_monthly_sales_summary
         ORDER BY {$monthColumn} DESC"
    )->fetchAll();

    if ($selectedMonth === '' && !empty($monthOptions)) {
        $selectedMonth = (string) $monthOptions[0]['report_month'];
    }

    $categoryOptions = $db->query(
        "SELECT DISTINCT category
         FROM vw_monthly_price_analysis
         ORDER BY category ASC"
    )->fetchAll();

    $medicineSql = "SELECT {$monthColumn} AS report_month, medicine_name, category, units_sold, avg_unit_price, revenue
                    FROM vw_monthly_medicine_sales
                    WHERE {$monthColumn} = :month_value
                      AND (:category_filter = '' OR category = :category_value)
                      AND (:search_filter = '' OR medicine_name LIKE :search_value)
                    ORDER BY {$monthColumn} DESC, revenue DESC
                    LIMIT 500";
    $medicineStmt = $db->prepare($medicineSql);
    $medicineStmt->execute([
        'month_value' => $selectedMonth,
        'category_filter' => $selectedCategory,
        'category_value' => $selectedCategory,
        'search_filter' => $searchMedicine,
        'search_value' => '%' . $searchMedicine . '%',
    ]);
    $medicineRows = $medicineStmt->fetchAll();

    $summarySql = "SELECT report_month,
                          SUM(units_sold) AS total_units_sold,
                          ROUND(SUM(revenue), 2) AS total_revenue,
                          ROUND(AVG(avg_unit_price), 2) AS avg_sale_price
                   FROM (
                       SELECT {$monthColumn} AS report_month, units_sold, revenue, avg_unit_price
                       FROM vw_monthly_medicine_sales
                       WHERE {$monthColumn} = :month_value
                         AND (:category_filter = '' OR category = :category_value)
                         AND (:search_filter = '' OR medicine_name LIKE :search_value)
                   ) filtered_sales
                   GROUP BY report_month";
    $summaryStmt = $db->prepare($summarySql);
    $summaryStmt->execute([
        'month_value' => $selectedMonth,
        'category_filter' => $selectedCategory,
        'category_value' => $selectedCategory,
        'search_filter' => $searchMedicine,
        'search_value' => '%' . $searchMedicine . '%',
    ]);
    $summaryRows = $summaryStmt->fetchAll();

    $priceSql = "SELECT report_month,
                        category,
                        ROUND(MIN(avg_unit_price), 2) AS min_price,
                        ROUND(
                            CASE WHEN SUM(units_sold) > 0
                                THEN SUM(revenue) / SUM(units_sold)
                                ELSE 0
                            END,
                            2
                        ) AS avg_price,
                        ROUND(MAX(avg_unit_price), 2) AS max_price
                 FROM (
                     SELECT {$monthColumn} AS report_month,
                            category,
                            units_sold,
                            revenue,
                            avg_unit_price
                     FROM vw_monthly_medicine_sales
                     WHERE {$monthColumn} = :month_value
                       AND (:category_filter = '' OR category = :category_value)
                       AND (:search_filter = '' OR medicine_name LIKE :search_value)
                 ) filtered_prices
                 GROUP BY report_month, category
                 ORDER BY report_month DESC, category ASC";
    $priceStmt = $db->prepare($priceSql);
    $priceStmt->execute([
        'month_value' => $selectedMonth,
        'category_filter' => $selectedCategory,
        'category_value' => $selectedCategory,
        'search_filter' => $searchMedicine,
        'search_value' => '%' . $searchMedicine . '%',
    ]);
    $priceRows = $priceStmt->fetchAll();
} catch (Throwable $e) {
    $analysisReady = false;
    $analysisError = t('analysis_error_prefix') . ' ' . $e->getMessage();
}

if ($analysisReady) {
    $filteredRevenue = (float) array_sum(array_map(static fn(array $r): float => (float) $r['revenue'], $medicineRows));
    $filteredUnits = (int) array_sum(array_map(static fn(array $r): int => (int) $r['units_sold'], $medicineRows));
}

renderHeader(t('title_monthly'));
?>

<?php if (!$analysisReady): ?>
    <div class="bg-rose-100 text-rose-700 border border-rose-200 rounded-xl p-4 text-sm">
        <p class="font-semibold mb-1"><?= e(t('analysis_error_title')) ?></p>
        <p><?= e($analysisError) ?></p>
    </div>
<?php else: ?>
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow">
            <p class="text-sm text-slate-500"><?= e(t('analysis_selected_month')) ?></p>
            <p class="text-3xl font-bold text-slate-800"><?= e($selectedMonth) ?></p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow">
            <p class="text-sm text-slate-500"><?= e(t('analysis_filtered_revenue')) ?></p>
            <p class="text-3xl font-bold text-emerald-600">
                <?= number_format($filteredRevenue, 2, ',', '.') ?> TL
            </p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow">
            <p class="text-sm text-slate-500"><?= e(t('analysis_filtered_units')) ?></p>
            <p class="text-3xl font-bold text-sky-700">
                <?= number_format((float) $filteredUnits, 0, ',', '.') ?>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow mb-6">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('analysis_filters')) ?></h3>
        <form method="get" class="grid md:grid-cols-4 gap-3">
            <select name="month" class="border rounded-lg px-3 py-2">
                <option value=""><?= e(t('analysis_all_months')) ?></option>
                <?php foreach ($monthOptions as $option): ?>
                    <?php $month = (string) $option['report_month']; ?>
                    <option value="<?= e($month) ?>" <?= $selectedMonth === $month ? 'selected' : '' ?>>
                        <?= e($month) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="category" class="border rounded-lg px-3 py-2">
                <option value=""><?= e(t('analysis_all_categories')) ?></option>
                <?php foreach ($categoryOptions as $option): ?>
                    <?php $category = (string) $option['category']; ?>
                    <option value="<?= e($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                        <?= e($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input
                type="text"
                name="q"
                value="<?= e($searchMedicine) ?>"
                class="border rounded-lg px-3 py-2"
                placeholder="<?= e(t('analysis_search_ph')) ?>"
            >
            <div class="flex gap-2">
                <button class="flex-1 bg-slate-900 text-white rounded-lg py-2"><?= e(t('analysis_apply')) ?></button>
                <a class="flex-1 text-center border rounded-lg py-2 hover:bg-slate-50" href="<?= e(urlWithLangOnly()) ?>"><?= e(t('analysis_clear')) ?></a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl p-5 shadow mb-6">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('analysis_summary')) ?></h3>
        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="p-2"><?= e(t('analysis_th_month')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_units')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_revenue')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_avg_price')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summaryRows as $row): ?>
                        <tr class="border-b">
                            <td class="p-2"><?= e((string) $row['report_month']) ?></td>
                            <td class="p-2"><?= (int) $row['total_units_sold'] ?></td>
                            <td class="p-2 text-emerald-700"><?= number_format((float) $row['total_revenue'], 2, ',', '.') ?> TL</td>
                            <td class="p-2"><?= number_format((float) $row['avg_sale_price'], 2, ',', '.') ?> TL</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow mb-6">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('analysis_medicine_detail')) ?></h3>
        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="p-2"><?= e(t('analysis_th_month')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_medicine')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_category')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_count')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_avg_unit')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_revenue_col')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$medicineRows): ?>
                        <tr>
                            <td class="p-3 text-slate-500" colspan="6"><?= e(t('analysis_empty_medicine')) ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicineRows as $row): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= e((string) $row['report_month']) ?></td>
                                <td class="p-2 font-medium"><?= e((string) ($row['medicine_name'] ?: '-')) ?></td>
                                <td class="p-2"><?= e((string) $row['category']) ?></td>
                                <td class="p-2"><?= (int) $row['units_sold'] ?></td>
                                <td class="p-2"><?= number_format((float) $row['avg_unit_price'], 2, ',', '.') ?> TL</td>
                                <td class="p-2 text-sky-700"><?= number_format((float) $row['revenue'], 2, ',', '.') ?> TL</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('analysis_price_title')) ?></h3>
        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="p-2"><?= e(t('analysis_th_month')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_category')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_min')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_avg')) ?></th>
                        <th class="p-2"><?= e(t('analysis_th_max')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$priceRows): ?>
                        <tr>
                            <td class="p-3 text-slate-500" colspan="5"><?= e(t('analysis_empty_price')) ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($priceRows as $row): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= e((string) $row['report_month']) ?></td>
                                <td class="p-2"><?= e((string) $row['category']) ?></td>
                                <td class="p-2"><?= number_format((float) $row['min_price'], 2, ',', '.') ?> TL</td>
                                <td class="p-2"><?= number_format((float) $row['avg_price'], 2, ',', '.') ?> TL</td>
                                <td class="p-2"><?= number_format((float) $row['max_price'], 2, ',', '.') ?> TL</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php renderFooter(); ?>
