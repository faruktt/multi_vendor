import ftplib
import os
import sys

FTP_HOST = 'srv790.hstgr.io'
FTP_USER = 'u311796885.ebsbazar.com'
FTP_PASS = 'Ebsbazar.com12'
REMOTE_ROOT = '/public_html'

FILES = [
    'app/Models/Product.php',
    'app/Http/Controllers/Shop/CartController.php',
    'app/Http/Controllers/Shop/HomeController.php',
    'app/Http/Controllers/Shop/ProductController.php',
    'resources/views/shop/layout.blade.php',
    'resources/views/shop/partials/quick-view-drawer.blade.php',
    'resources/views/shop/partials/shelf-product-card.blade.php',
    'resources/views/shop/products/show.blade.php',
]

def ensure_remote_dir(ftp, remote_dir):
    parts = [p for p in remote_dir.split('/') if p]
    current = ''
    for p in parts:
        current += '/' + p
        try:
            ftp.cwd(current)
        except Exception:
            try:
                ftp.mkd(current)
            except Exception as e:
                pass

def main():
    print(f"Connecting to FTP {FTP_HOST}...")
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, 21, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    print("Logged in successfully.")

    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))

    for rel_path in FILES:
        local_path = os.path.join(base_dir, rel_path.replace('/', os.sep))
        remote_path = REMOTE_ROOT + '/' + rel_path
        remote_dir = os.path.dirname(remote_path)

        if not os.path.exists(local_path):
            print(f"[MISSING] {local_path}")
            continue

        ensure_remote_dir(ftp, remote_dir)

        print(f"Uploading {rel_path} -> {remote_path}...")
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        print(f"[OK] {rel_path}")

    # Clear remote view cache files
    print("Clearing remote view cache...")
    try:
        views_dir = REMOTE_ROOT + '/storage/framework/views'
        ftp.cwd(views_dir)
        files = ftp.nlst()
        for f in files:
            if f.endswith('.php'):
                try:
                    ftp.delete(f)
                except Exception:
                    pass
        print(f"Cleared {len(files)} cached view files.")
    except Exception as e:
        print(f"Notice on view cache: {e}")

    # Clear bootstrap cache if any
    try:
        bcache_dir = REMOTE_ROOT + '/bootstrap/cache'
        ftp.cwd(bcache_dir)
        for f in ['packages.php', 'services.php', 'config.php', 'routes-v7.php']:
            try:
                ftp.delete(f)
                print(f"Deleted {f}")
            except Exception:
                pass
    except Exception as e:
        print(f"Notice on bootstrap cache: {e}")

    ftp.quit()
    print("Deployment finished successfully!")

if __name__ == '__main__':
    main()
