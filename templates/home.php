<div class="container">
    <?php if (!empty($settings['site_description'])): ?>
        <div class="site-intro">
            <p class="lead"><?= e($settings['site_description']) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="posts-grid">
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <p>Поки що немає опублікованих постів.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="post-meta">
                        <time datetime="<?= e($post['published_at']) ?>">
                            <?= formatDate($post['published_at']) ?>
                        </time>
                        <span class="views">
                            <i class="fas fa-eye"></i> <?= formatViews($post['views']) ?>
                        </span>
                    </div>
                    
                    <h2 class="post-title">
                        <a href="<?= siteUrl($post['slug']) ?>"><?= e($post['title']) ?></a>
                    </h2>
                    
                    <?php if (!empty($post['excerpt'])): ?>
                        <div class="post-excerpt">
                            <?= e($post['excerpt']) ?>
                        </div>
                    <?php else: ?>
                        <div class="post-excerpt">
                            <?= e(truncateText(strip_tags(parseMarkdown($post['content'])), 200)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    $post_tags = getPostTags($post['id']);
                    if (!empty($post_tags)):
                    ?>
                        <div class="post-tags">
                            <?php foreach ($post_tags as $tag): ?>
                                <a href="<?= siteUrl('tag/' . $tag['slug']) ?>" class="tag">
                                    <?= e($tag['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= siteUrl($post['slug']) ?>" class="read-more">
                        Читати далі <i class="fas fa-arrow-right"></i>
                    </a>
                    
                    <?php
                    $comments_count = getApprovedCommentsCount($post['id']);
                    if ($comments_count > 0):
                    ?>
                        <div class="post-comments-count">
                            <i class="fas fa-comments"></i> <?= $comments_count ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <?= pagination($page, $total_pages, siteUrl()) ?>
    <?php endif; ?>
</div>