import ftplib
import os
import sys

FTP_HOST = 'srv790.hstgr.io'
FTP_USER = 'u311796885.ebsbazar.com'
FTP_PASS = 'Ebsbazar.com12'
REMOTE_ROOT = '/public_html'

FILES = [
    'app/Services/ProductImageService.php',
    'app/Console/Commands/ResizeProductImages.php',
    'resources/views/shop/partials/shelf-product-card.blade.php',
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

    # Also upload the uncropped version of the demo product's image
    demo_local = os.path.join(base_dir, 'public', 'uploads', 'products', 'img_6af055f3981da5.21688548.jpg')
    if os.path.exists(demo_local):
        demo_remote = REMOTE_ROOT + '/public/uploads/products/img_6af055f3981da5.21688548.jpg'
        print(f"Updating demo product image -> {demo_remote}...")
        with open(demo_local, 'rb') as f:
            ftp.storbinary(f'STOR {demo_remote}', f)
        print("[OK] Demo product image restored to uncropped version!")

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

    ftp.quit()
    print("Deployment finished successfully!")

if __name__ == '__main__':
    main()
