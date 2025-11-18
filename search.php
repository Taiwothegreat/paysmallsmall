<?php if (!empty($results)): ?>
    <h3 style="margin-top:30px;">Search Results</h3>

    <div class="row">

        <?php foreach ($results as $product): ?>
            <div class="col-xs-6 col-sm-4 col-md-3" style="margin-bottom:25px; text-align:center;">

                <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration:none; color:#333;">

                    <div style="border:1px solid #ddd; padding:10px; border-radius:5px; background:#fff;">

                        <img src="<?php echo $product['image']; ?>" 
                             alt="<?php echo $product['name']; ?>"
                             style="width:200px; height:200px; object-fit:cover; border-radius:4px;">

                        <h4 style="font-size:16px; margin-top:10px; height:40px; overflow:hidden;">
                            <?php echo $product['name']; ?>
                        </h4>

                        <p style="font-weight:bold; color:#bc933b; margin:0;">
                            ₦<?php echo number_format($product['price']); ?>
                        </p>

                    </div>
                </a>

            </div>
        <?php endforeach; ?>

    </div>

<?php else: ?>

    <p>No products found for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"</p>

<?php endif; ?>
