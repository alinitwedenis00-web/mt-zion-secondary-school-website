</main>
<footer class="footer" id="page-bottom">
    <div class="footer-main">
        <div class="footer-brand">
            <img src="assets/images/school/BADGE.jpg" alt="Mt Zion Secondary School badge">
            <h3><?= e(setting('school_name', 'Mt Zion Secondary School Mubende')) ?></h3>
            <p><?= nl2br(e(setting('footer_text', 'A Centre of Excellence. Determination with Devotion for Excellence.'))) ?></p>
        </div>
        <div>
            <h4>Contact</h4>
            <p><?= nl2br(e(setting('address', 'Mt Zion, Mubende, Uganda'))) ?></p>
            <p><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', setting('phone', '+256772824530'))) ?>"><?= e(setting('phone', '+256 772824530')) ?></a></p>
            <p><a href="mailto:<?= e(setting('email', 'mtzionssmubende2010@gmail.com')) ?>"><?= e(setting('email', 'mtzionssmubende2010@gmail.com')) ?></a></p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <a href="index.php">Home</a><a href="about.php">About Us</a><a href="services.php">Academics</a>
            <a href="admissions.php">Admissions</a><a href="admission-track.php">Track Admission</a>
            <a href="message-track.php">Track Message</a><a href="gallery.php">Gallery</a>
            <a href="news.php">News</a><a href="portfolio.php">Portfolio</a><a href="contact.php">Contact Us</a>
        </div>
        <div class="footer-social">
            <h4>Follow Us</h4>
            <p><?= nl2br(e(setting('social_text', 'Follow our official school channels.'))) ?></p>
            <div class="socials" aria-label="School social media links">
                <a class="social-link facebook" href="https://www.facebook.com/mtzionssmubende/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.5 1.6-1.5h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.3v3.2h2.8V21h3.4z"/></svg>
                </a>
                <a class="social-link youtube" href="https://www.youtube.com/@Mtzionmubende12" target="_blank" rel="noopener noreferrer" aria-label="YouTube" title="YouTube">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z"/></svg>
                </a>
                <a class="social-link tiktok" href="https://www.tiktok.com/@mtzionssmubende2010" target="_blank" rel="noopener noreferrer" aria-label="TikTok" title="TikTok">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.6 6.2a5.7 5.7 0 0 1-3.5-3.8V2h-3.8v13.1a2.8 2.8 0 1 1-2.8-2.8c.3 0 .6 0 .9.1V8.5a6.8 6.8 0 1 0 5.7 6.7V9a9.4 9.4 0 0 0 5.4 1.7V7a5.7 5.7 0 0 1-1.9-.8Z"/></svg>
                </a>
                <a class="social-link whatsapp" href="https://wa.me/256772824530" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 2 17.7L.5 23.5l6-1.6A11.8 11.8 0 0 0 20.5 3.5ZM12 21a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.5.9.9-3.4-.2-.4A9.8 9.8 0 1 1 12 21Zm5.4-7.3c-.3-.2-1.7-.9-2-.9s-.5-.2-.7.2-.8.9-1 1.1-.4.2-.7.1a8 8 0 0 1-2.4-1.5 9 9 0 0 1-1.6-2c-.2-.3 0-.5.2-.7l.5-.6.2-.5c.1-.2 0-.4 0-.6l-.9-2.1c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.4s-1.2 1.2-1.2 2.8 1.2 3.2 1.4 3.4a11.2 11.2 0 0 0 4.3 3.8c.6.3 1.1.5 1.5.6.6.2 1.2.2 1.7.1.5-.1 1.7-.7 1.9-1.4s.2-1.3.2-1.4-.2-.2-.5-.4Z"/></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="copyright">© <?= date('Y') ?> <?= e(setting('school_name', 'Mt Zion Secondary School Mubende')) ?> | Contact the developer via alinitwedenis00@gmail.com | +256 749887957</div>
</footer>
<div class="page-controls" aria-label="Page navigation">
    <a class="scroll-control scroll-up" href="#page-top" aria-label="Scroll to top" title="Scroll to top"><span aria-hidden="true">↑</span></a>
    <a class="scroll-control scroll-down" href="#page-bottom" aria-label="Scroll to bottom" title="Scroll to bottom"><span aria-hidden="true">↓</span></a>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
