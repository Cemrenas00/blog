CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$/DzGHp9MmtuvPmXBsRjMhe65iWfGRqv3cECCdwrbicV8ImlhbVBLu');

INSERT INTO categories (name, slug) VALUES 
('Teknoloji', 'teknoloji'),
('Yazılım', 'yazilim'),
('Tasarım', 'tasarim'),
('Günlük', 'gunluk');

INSERT INTO posts (category_id, title, slug, content, status) VALUES 
(1, 'Yapay Zeka ve Geleceğimiz', 'yapay-zeka-ve-gelecegimiz', 
'<p>Yapay zeka teknolojisi hayatımızın her alanında köklü değişiklikler yaratmaya devam ediyor. Sağlıktan eğitime, ulaşımdan eğlenceye kadar birçok sektör bu dönüşümden etkileniyor.</p><p>Özellikle son yıllarda büyük dil modellerinin gelişmesi, yapay zekanın potansiyelini daha da belirgin hale getirdi. Bu modeller doğal dili anlama ve üretme konusunda inanılmaz yeteneklere sahip.</p><p>Gelecekte yapay zekanın daha da yaygınlaşması ve daha karmaşık görevleri yerine getirmesi bekleniyor.</p>', 'published'),

(2, 'PHP ile Modern Web Geliştirme', 'php-ile-modern-web-gelistirme', 
'<p>PHP dünyasında modern geliştirme pratikleri hızla evrilmekte. PHP 8.x sürümleri ile gelen JIT derleyici, named arguments, match expressions gibi özellikler dilin gücünü artırdı.</p><p>Composer paket yöneticisi, PSR standartları ve modern framework''ler sayesinde PHP ile enterprise düzeyde uygulamalar geliştirmek artık çok daha kolay.</p><p>Laravel, Symfony gibi framework''ler ile backend geliştirme süreçleri oldukça hızlandı.</p>', 'published'),

(3, 'Responsive Tasarım İlkeleri', 'responsive-tasarim-ilkeleri', 
'<p>Responsive tasarım artık bir tercih değil, zorunluluk. Mobil cihazlardan gelen internet trafiği masaüstünü çoktan geçti.</p><p>CSS Grid ve Flexbox gibi modern layout teknikleri ile responsive tasarım yapmak her zamankinden kolay. Media query''ler ile farklı ekran boyutlarına uyum sağlamak critical öneme sahip.</p><p>Mobile-first yaklaşımı benimsendiğinde, tasarım süreci çok daha verimli ilerliyor.</p>', 'published'),

(1, 'Web Güvenliği: SQL Injection ve XSS', 'web-guvenligi-sql-injection-xss', 
'<p>Web uygulamalarında güvenlik her zaman ön planda tutulmalıdır. SQL Injection ve Cross-Site Scripting (XSS) en yaygın güvenlik açıklarının başında gelir.</p><p>PDO prepared statements kullanarak SQL injection saldırılarını önleyebilirsiniz. htmlspecialchars() fonksiyonu ile XSS saldırılarına karşı korunabilirsiniz.</p><p>CSRF tokenleri ve güvenli oturum yönetimi de kritik güvenlik katmanlarıdır.</p>', 'published');
