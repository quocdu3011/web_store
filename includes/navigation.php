<?php
$parent_categories = get_parent_categories();
$child_categories = [];
foreach ($parent_categories as $parent) {
    $child_categories[$parent['id']] = get_child_categories($parent['id']);
}
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav">
                <?php foreach ($parent_categories as $category): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?php echo sanitize_output($category['name']); ?>
                        </a>
                        <?php if (!empty($child_categories[$category['id']])): ?>
                            <ul class="dropdown-menu">
                                <?php foreach ($child_categories[$category['id']] as $subcategory): ?>
                                    <li>
                                        <a class="dropdown-item" href="category.php?slug=<?php echo sanitize_output($subcategory['slug']); ?>">
                                            <?php echo sanitize_output($subcategory['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="deals.php">Khuyến Mãi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="new-arrivals.php">Hàng Mới Về</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Liên Hệ</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 