<?php
session_start();
$cid = $_GET['id'];

require 'includes/connection.php';

$sql = mysqli_prepare($conn, "SELECT num_si FROM site_instruction WHERE ddId = ?");
mysqli_stmt_bind_param($sql, "s", $cid);
mysqli_stmt_execute($sql);

// 1. Get the result set from the prepared statement
$result = mysqli_stmt_get_result($sql);

// 2. Fetch the row from the result set
if ($row = mysqli_fetch_assoc($result)) {
    $num_si = $row['num_si'];
} else {
    // Handle case where no row was found
    $num_si = null; 
}


// Fetch attachments
$stmt = mysqli_prepare($conn, "SELECT * FROM si_attachment WHERE num_si = ?");
mysqli_stmt_bind_param($stmt, "s", $num_si);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$attachments = mysqli_fetch_all($result, MYSQLI_ASSOC);

$imgCol = 'file_name'; // Fallback
if (count($attachments) > 0) {
    if (isset($attachments[0]['attachment'])) $imgCol = 'attachment';
    elseif (isset($attachments[0]['url'])) $imgCol = 'url';
    elseif (isset($attachments[0]['image'])) $imgCol = 'image';
    elseif (isset($attachments[0]['file'])) $imgCol = 'file';
    elseif (isset($attachments[0]['file_name'])) $imgCol = 'file_name';
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartCut - Lampiran</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .card { border-radius: 16px; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #e2e8f0;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        .gallery-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 116, 66, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        .gallery-overlay i {
            color: #fff;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .lightbox {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.95);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        .lightbox.active { display: flex; }
        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            text-align: center;
        }
        .lightbox-img {
            max-width: 100%;
            max-height: 85vh;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            object-fit: contain;
        }
        .lightbox-close, .lightbox-prev, .lightbox-next {
            position: absolute;
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .lightbox-close:hover, .lightbox-prev:hover, .lightbox-next:hover {
            background: #007442;
            transform: scale(1.1);
        }
        .lightbox-close {
            top: 20px; right: 20px;
            width: 45px; height: 45px;
            font-size: 1.8rem;
            z-index: 10000;
        }
        .lightbox-prev, .lightbox-next {
            top: 50%;
            transform: translateY(-50%);
            width: 55px; height: 55px;
            font-size: 1.5rem;
            z-index: 10000;
        }
        .lightbox-prev:hover { transform: translateY(-50%) scale(1.1); }
        .lightbox-next:hover { transform: translateY(-50%) scale(1.1); }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }
        
        @media (max-width: 768px) {
            .lightbox-prev { left: 10px; width: 40px; height: 40px; font-size: 1.2rem; }
            .lightbox-next { right: 10px; width: 40px; height: 40px; font-size: 1.2rem; }
            .lightbox-close { top: 10px; right: 10px; width: 35px; height: 35px; font-size: 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?php include 'partials/navbar.php' ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?php include 'partials/sidebar.php' ?>
            <!-- partial -->
            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <a href="sitedaily_records.php?id=<?php echo $cid; ?>" class="btn btn-light shadow-sm">
                                <i class="icon-arrow-left"></i> Kembali ke Rekod Harian
                            </a>
                        </div>
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title text-dark font-weight-bold mb-4">Lampiran</h4>
                                    
                                    <?php if(count($attachments) > 0): ?>
                                        <div class="gallery-grid">
                                            <?php foreach($attachments as $index => $att): 
                                                $src = $att[$imgCol];
                                            ?>
                                                <div class="gallery-item" onclick="openLightbox(<?php echo $index; ?>)">
                                                    <img src="<?php echo htmlspecialchars($src); ?>" alt="Lampiran <?php echo $index + 1; ?>" onerror="this.src='assets/images/placeholder.png';">
                                                    <div class="gallery-overlay">
                                                        <i class="icon-magnifier-add"></i>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert text-center py-5" style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px;">
                                            <i class="icon-picture text-muted mb-2" style="font-size: 48px; opacity: 0.5;"></i>
                                            <h5 class="text-dark font-weight-bold mt-4">Tiada Lampiran Ditemui</h5>
                                            <p class="text-muted mb-0">Tiada imej dilampirkan pada rekod ini.</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Element -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()" title="Tutup">&times;</button>
        <button class="lightbox-prev" onclick="changeImage(-1)" title="Sebelumnya"><i class="icon-arrow-left"></i></button>
        <button class="lightbox-next" onclick="changeImage(1)" title="Seterusnya"><i class="icon-arrow-right"></i></button>
        <div class="lightbox-content">
            <img src="" alt="Imej skrin penuh" class="lightbox-img" id="lightboxImg">
        </div>
    </div>
    
    <script>
        const images = <?php 
            $imgArray = [];
            foreach($attachments as $att) {
                $imgArray[] = $att[$imgCol];
            }
            echo json_encode($imgArray); 
        ?>;
        
        let currentIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');

        function openLightbox(index) {
            if(images.length === 0) return;
            currentIndex = index;
            updateLightboxImage();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function changeImage(direction) {
            currentIndex += direction;
            if (currentIndex >= images.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = images.length - 1;
            updateLightboxImage();
        }

        function updateLightboxImage() {
            lightboxImg.src = images[currentIndex];
        }

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target === document.querySelector('.lightbox-content')) {
                closeLightbox();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'ArrowLeft') changeImage(-1);
        });
    </script>
</body>
</html>
