</main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?= e($settings['site_name'] ?? 'Блог') ?></h3>
                    <p><?= e($settings['site_description'] ?? '') ?></p>
                </div>
                
                <div class="footer-section">
                    <h4>Навігація</h4>
                    <ul>
                        <li><a href="<?= siteUrl() ?>">Головна</a></li>
                        <?php
                        $footer_pages = getAllPublishedPages();
                        foreach ($footer_pages as $footer_page):
                        ?>
                            <li><a href="<?= siteUrl($footer_page['slug']) ?>"><?= e($footer_page['title']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= siteUrl('rss') ?>">RSS</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Популярні теги</h4>
                    <div class="tag-cloud">
                        <?php
                        $popular_tags = getPopularTags(8);
                        foreach ($popular_tags as $footer_tag):
                        ?>
                            <a href="<?= siteUrl('tag/' . $footer_tag['slug']) ?>" class="tag">
                                <?= e($footer_tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e($settings['site_name'] ?? 'Блог') ?>. Всі права захищено.</p>
                <p>
                    <a href="<?= siteUrl('admin') ?>">Адмін</a>
                </p>
            </div>
        </div>
    </footer>
    
    <script src="<?= assetUrl('js/main.js') ?>"></script>
</body>
</html>