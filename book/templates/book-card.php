<div class="book-card">
    <a class="book-link" href="<?php echo get_permalink(); ?>">
        <div class="book-img">
            <?php if (has_post_thumbnail()) :
                the_post_thumbnail();
            endif; ?>
        </div>
        <h4 class="h4 fw-semibold"><?php the_title(); ?></h4>
        <?php $author = get_post_meta(get_the_ID(), '_book_author', true); ?>
        <span>by <?= $author ?></span>
    </a>
</div>