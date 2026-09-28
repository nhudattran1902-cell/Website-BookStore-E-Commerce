//
document.addEventListener("DOMContentLoaded", function () {
    // Lấy tất cả các ảnh có class lazy-img
    const images = document.querySelectorAll(".lazy-img");

    images.forEach((img) => {
        // Hàm chạy khi ảnh tải thành công
        const handleImageLoad = () => {
            img.classList.add("loaded"); // Hiện ảnh lên
            const spinner = img.previousElementSibling;
            if (spinner && spinner.classList.contains("loading-spinner")) {
                spinner.style.display = "none"; // Ẩn vòng xoay
            }
        };

        // Hàm chạy khi mất mạng hoặc link ảnh bị hỏng
        const handleImageError = () => {
            const wrapper = img.parentElement;
            // Xóa vòng xoay và ảnh bị hỏng, thay thế bằng Icon FontAwesome
            wrapper.innerHTML =
                '<div class="text-center text-muted d-flex flex-column justify-content-center align-items-center h-100" style="background-color: #f3f3f3;"><i class="fas fa-image fs-3 mb-2 text-secondary"></i><small style="font-size: 0.75rem;">No Image</small></div>';
        };

        // Kiểm tra xem ảnh đã có sẵn trong cache chưa
        if (img.complete) {
            if (img.naturalWidth !== 0) {
                handleImageLoad();
            } else {
                handleImageError();
            }
        } else {
            // Nếu chưa có, gán sự kiện đợi tải từ mạng
            img.addEventListener("load", handleImageLoad);
            img.addEventListener("error", handleImageError);
        }
    });
});
