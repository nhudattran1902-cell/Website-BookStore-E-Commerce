
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

document.addEventListener("submit", async (event) => {
    const form = event.target.closest("[data-cart-form]");

    if (!form) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector("[data-cart-submit]");
    if (!button || form.dataset.submitting === "true") {
        return;
    }

    const originalMarkup = button.innerHTML;
    const wasDisabled = button.disabled;

    form.dataset.submitting = "true";
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Đang thêm...';

    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: new FormData(form),
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        const contentType = response.headers.get("content-type") || "";
        const payload = contentType.includes("application/json") ? await response.json() : {};

        if (!response.ok) {
            if (response.status === 401) {
                throw new Error("Vui lòng đăng nhập để thêm sách vào giỏ hàng.");
            }

            const validationMessage = Object.values(payload.errors || {}).flat()[0];
            throw new Error(validationMessage || payload.message || "Không thể thêm sách vào giỏ hàng.");
        }

        const cartCount = document.querySelector("[data-cart-count]");
        if (cartCount && Number.isFinite(Number(payload.cart_count))) {
            const count = Number(payload.cart_count);
            cartCount.textContent = count;
            cartCount.setAttribute("aria-label", `${count} sản phẩm trong giỏ`);
            cartCount.classList.toggle("d-none", count === 0);
        }

        showCartToast(payload.message || `Đã thêm ${payload.book_name} vào giỏ hàng!`);
    } catch (error) {
        showCartToast(error.message || "Đã xảy ra lỗi. Vui lòng thử lại.", true);
    } finally {
        button.innerHTML = originalMarkup;
        button.disabled = wasDisabled;
        delete form.dataset.submitting;
    }
});

function showCartToast(message, isError = false) {
    const region = document.getElementById("cart-toast-region");
    if (!region) {
        return;
    }

    const toast = document.createElement("div");
    toast.className = `cart-toast${isError ? " cart-toast--error" : ""}`;
    toast.setAttribute("role", isError ? "alert" : "status");
    toast.textContent = message;
    region.append(toast);

    requestAnimationFrame(() => toast.classList.add("is-visible"));

    window.setTimeout(() => {
        toast.classList.remove("is-visible");
        window.setTimeout(() => toast.remove(), 250);
    }, 3000);
}
