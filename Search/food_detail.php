<?php
include '../FilePHP/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "ไม่พบอาหารที่ต้องการดูรายละเอียด";
    exit;
}

$sql = "SELECT 
    f.id AS food_id,
    f.name,
    f.food_img AS image,
    f.description,
    f.recipe,
    f.calories_img,
    f.history,
    GROUP_CONCAT(DISTINCT e.name) AS mood,
    GROUP_CONCAT(DISTINCT CONCAT(h.name, ' (', h.properties, ')')) AS herbs
FROM foods f
LEFT JOIN food_emotions fe ON f.id = fe.food_id
LEFT JOIN emotions e ON fe.emotion_id = e.id
LEFT JOIN food_herbs fh ON f.id = fh.food_id
LEFT JOIN herbs h ON fh.herb_id = h.id
WHERE f.id = $id
GROUP BY f.id
LIMIT 1";

$result = $conn->query($sql);
$food = $result ? $result->fetch_assoc() : null;

// แปลง string เป็น array สำหรับการแสดงผล
$moodArray = $food && $food['mood'] ? explode(', ', $food['mood']) : [];
$herbsArray = $food && $food['herbs'] ? explode(', ', $food['herbs']) : [];
// ดึงวัตถุดิบจาก recipe (คั่นด้วย ,)
$ingredientsArray = $food && $food['recipe'] ? array_map('trim', explode(',', $food['recipe'])) : [];
$recipeSteps = $food && $food['recipe'] ? array_filter(explode("\n", $food['recipe'])) : [];
$descriptionSteps = $food && $food['description'] ? array_filter(explode("\n", $food['description'])) : [];

// --- NEW: ดึงเฉพาะชื่อสมุนไพรจาก herbsArray (ตัดส่วน properties) ---
$herbNames = [];
if (!empty($herbsArray)) {
    foreach ($herbsArray as $h) {
        if (preg_match('/^([^\(]+)/', $h, $m)) {
            $herbNames[] = trim($m[1]);
        } else {
            $herbNames[] = trim($h);
        }
    }
}
$combinedIngredients = array_values(array_filter(array_unique(array_merge($ingredientsArray, $herbNames))));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title><?= $food ? htmlspecialchars($food['name']) : "ไม่พบอาหาร" ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        .masonry-container {
            column-count: 3;
            column-gap: 2rem;
            column-fill: balance;
        }
        
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 2rem;
            display: inline-block;
            width: 100%;
        }
        
        @media (max-width: 1024px) {
            .masonry-container {
                column-count: 2;
            }
        }
        
        @media (max-width: 640px) {
            .masonry-container {
                column-count: 1;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <?php if ($food): ?>
        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900">รายละเอียดอาหาร</h1>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-10">
            <!-- Masonry Layout -->
            <div class="masonry-container">
                
                <!-- Food Image Card -->
                <div class="masonry-item">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="aspect-[4/3]">
                            <img 
                                src="/NLPTEST/uploads/foods/<?= htmlspecialchars($food['image']) ?>" 
                                alt="<?= htmlspecialchars($food['name']) ?>" 
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div class="p-6">
                            <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($food['name']) ?></h1>
                        </div>
                    </div>
                </div>

                <!-- Mood Card -->
                <div class="masonry-item">
                    <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="heart" class="w-6 h-6 text-indigo-600"></i>
                            <h3 class="text-xl font-semibold text-indigo-900">อารมณ์ที่เหมาะสม</h3>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($moodArray as $mood): ?>
                                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-white text-indigo-800 shadow-sm">
                                    <?= htmlspecialchars(trim($mood)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Ingredients Card -->
                <div class="masonry-item">
                    <div class="bg-orange-50 p-6 rounded-xl border border-orange-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="shopping-cart" class="w-6 h-6 text-orange-600"></i>
                            <h3 class="text-xl font-semibold text-orange-900">วัตถุดิบที่ใช้</h3>
                        </div>
                        <ol class="space-y-3">
                            <?php foreach ($combinedIngredients as $index => $ingredient): ?>
                                <li class="flex items-start gap-3">
                                    <span class="text-sm text-gray-700 leading-relaxed"><?= htmlspecialchars(trim($ingredient)) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>

                <!-- Herbs Card -->
                <div class="masonry-item">
                    <div class="bg-green-50 p-6 rounded-xl border border-green-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="leaf" class="w-6 h-6 text-green-600"></i>
                            <h3 class="text-xl font-semibold text-green-900">สมุนไพรในอาหาร</h3>
                        </div>
                        <div class="space-y-2">
                           <?php foreach ($herbsArray as $herb): ?>
                                <div class="text-sm text-gray-700 leading-relaxed">
                                    <?= htmlspecialchars(trim($herb)) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- History Card -->
                <div class="masonry-item">
                    <div class="bg-yellow-50 p-6 rounded-xl border border-yellow-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="book-open" class="w-6 h-6 text-yellow-600"></i>
                            <h3 class="text-xl font-semibold text-yellow-900">ประวัติความเป็นมา</h3>
                        </div>
                        <p class="text-gray-700 leading-relaxed text-sm whitespace-pre-line">
                            <?= $food['history'] ? htmlspecialchars($food['history']) : "ไม่มีข้อมูล" ?>
                        </p>
                    </div>
                </div>

                <!-- description Steps Card -->
                <div class="masonry-item">
                    <div class="bg-red-50 p-6 rounded-xl border border-red-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="chef-hat" class="w-6 h-6 text-red-600"></i>
                            <h3 class="text-xl font-semibold text-red-900">ขั้นตอนการทำ</h3>
                        </div>
                        <ol class="space-y-4">
                            <?php foreach ($descriptionSteps as $index => $step): ?>
                                <li class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                        <?= $index + 1 ?>
                                    </div>
                                    <span class="text-sm text-gray-700 leading-relaxed">
                                        <strong>ขั้นตอน <?= $index + 1 ?>:</strong>
                                        <?= htmlspecialchars(trim($step)) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>

                <!-- Calories Card -->
                <div class="masonry-item">
                    <div class="bg-pink-50 p-6 rounded-xl border border-pink-200 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <i data-lucide="bar-chart" class="w-6 h-6 text-pink-600"></i>
                            <h3 class="text-xl font-semibold text-pink-900">แคลอรี่ของเมนูนี้</h3>
                        </div>
                        <div class="flex justify-center">
                            <img 
                                src="/NLPTEST/uploads/cal/<?= htmlspecialchars($food['calories_img']) ?>" 
                                alt="ข้อมูลแคลอรี่" 
                                class="w-full rounded-lg shadow-md"
                            />
                        </div>
                    </div>
                </div>

                <!-- Back Button Card -->
                <div class="masonry-item">
                    <div class="bg-white p-6 rounded-xl shadow-lg border">
                        <div class="text-center">
                            <div class="mb-4">
                                <i data-lucide="arrow-left" class="w-12 h-12 text-indigo-600 mx-auto"></i>
                            </div>
                            <button 
                                onclick="history.back()" 
                                class="w-full inline-flex items-center justify-center px-6 py-4 bg-indigo-600 text-white text-lg font-medium rounded-lg hover:bg-indigo-700 transition shadow-md"
                            >
                                กลับหน้าก่อนหน้า
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    <?php else: ?>
        <!-- Error State -->
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <div class="mb-6">
                    <i data-lucide="alert-circle" class="w-20 h-20 text-red-500 mx-auto"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">ไม่พบข้อมูลอาหาร</h1>
                <p class="text-xl text-gray-600 mb-6">ไม่พบข้อมูลอาหารที่คุณต้องการ</p>
                <button 
                    onclick="history.back()" 
                    class="inline-flex items-center px-8 py-4 bg-indigo-600 text-white text-lg rounded-xl hover:bg-indigo-700 transition shadow-lg"
                >
                    <i data-lucide="arrow-left" class="w-5 h-5 mr-3"></i>
                    กลับ
                </button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        lucide.replace();
    </script>
</body>
</html>