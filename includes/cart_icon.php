<?php

?>
<!DOCTYPE html>
<html lang="vn">
<a href="cart.php" class="fixed-cart-icon" title="Giỏ hàng của bạn">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-count"><?php echo get_cart_count();; ?></span>
</a>
<style>
    .fixed-cart-icon {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #198754;
        /* Màu xanh Bootstrap */
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        z-index: 1100;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s, transform 0.3s;
    }

    .fixed-cart-icon:hover {
        background-color: #157347;
        /* đậm hơn khi hover */
        transform: scale(1.05);
    }

    .fixed-cart-icon i {
        font-size: 20px;
    }

    .fixed-cart-icon .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        color: white;
        font-size: 12px;
        padding: 2px 6px;
        border-radius: 50%;
    }
</style>
<script>
    const cartIcon = document.querySelector('.fixed-cart-icon');
    let lastScrollTop = 0;

    window.addEventListener("scroll", () => {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // if (scrollTop > lastScrollTop && scrollTop > 100) {
        //     // Cuộn xuống → ẩn
        //     cartIcon.style.opacity = "0";
        //     cartIcon.style.pointerEvents = "none";
        // } else {
        //     // Cuộn lên → hiện lại
        //     cartIcon.style.opacity = "1";
        //     cartIcon.style.pointerEvents = "auto";
        // }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });
</script>

</html>