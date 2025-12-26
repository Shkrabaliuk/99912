<?php
$page_title = $post['title'];
$meta_description = $post['excerpt'] ?? truncateText(strip_tags(parseMarkdown($post['content'])), 160);
?>

<div class="container">
    <article class="single-post">
        <header class="post-header">
            <h1 class="post-title"><?= e($post['title']) ?></h1>
            
            <div class="post-meta">
                <time datetime="<?= e($post['published_at']) ?>">
                    <?= formatDate($post['published_at']) ?>
                </time>
                <span class="views">
                    <i class="fas fa-eye"></i> <?= formatViews($post['views']) ?> переглядів
                </span>
                <span class="comments-count">
                    <i class="fas fa-comments"></i> <?= count($comments) ?> коментарів
                </span>
            </div>
            
            <?php if (!empty($tags)): ?>
                <div class="post-tags">
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= siteUrl('tag/' . $tag['slug']) ?>" class="tag">
                            <i class="fas fa-tag"></i> <?= e($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="post-content">
            <?= parseMarkdown($post['content']) ?>
        </div>
        
        <footer class="post-footer">
            <div class="share-buttons">
                <span>Поділитися:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(getCurrentUrl()) ?>" target="_blank" rel="noopener" class="share-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(getCurrentUrl()) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="share-btn twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://t.me/share/url?url=<?= urlencode(getCurrentUrl()) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="share-btn telegram">
                    <i class="fab fa-telegram-plane"></i>
                </a>
            </div>
        </footer>
    </article>
    
    <section class="comments-section">
        <h2>Коментарі (<?= count($comments) ?>)</h2>
        
        <?php if (!empty($comments)): ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong class="comment-author"><?= e($comment['author_name']) ?></strong>
                            <time class="comment-date"><?= timeAgo($comment['created_at']) ?></time>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(e($comment['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-comments">Поки що немає коментарів. Будьте першим!</p>
        <?php endif; ?>
        
        <div class="comment-form-wrapper">
            <h3>Залишити коментар</h3>
            
            <?php if (Session::flash('comment_success')): ?>
                <div class="alert alert-success">
                    Ваш коментар успішно відправлено і очікує модерації.
                </div>
            <?php endif; ?>
            
            <?php if (Session::flash('comment_error')): ?>
                <div class="alert alert-error">
                    <?= e(Session::flash('comment_error')) ?>
                </div>
            <?php endif; ?>
            
            <form class="comment-form" method="post" action="<?= siteUrl('add-comment') ?>">
                <?php $csrf_token = Security::generateCSRFToken(); ?>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="hidden" name="form_start_time" value="<?= time() ?>" id="formStartTime">
                
                <!-- Honeypot -->
                <input type="text" name="website" value="" class="honeypot" tabindex="-1" autocomplete="off">
                
                <div class="form-group">
                    <label for="author_name">Імʼя *</label>
                    <input type="text" id="author_name" name="author_name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="author_email">Email * (не публікується)</label>
                    <input type="email" id="author_email" name="author_email" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="content">Коментар *</label>
                    <textarea id="content" name="content" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Відправити коментар
                </button>
            </form>
        </div>
    </section>
    
    <?php
    $recent_posts = getRecentPosts(3, $post['id']);
    if (!empty($recent_posts)):
    ?>
        <aside class="related-posts">
            <h3>Читайте також</h3>
            <div class="related-posts-grid">
                <?php foreach ($recent_posts as $related): ?>
                    <article class="related-post">
                        <h4>
                            <a href="<?= siteUrl($related['slug']) ?>"><?= e($related['title']) ?></a>
                        </h4>
                        <time><?= formatDate($related['published_at']) ?></time>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    <?php endif; ?>
</div>