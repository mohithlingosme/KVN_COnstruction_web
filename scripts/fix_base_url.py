import os
import glob

files = glob.glob(r'c:\xampp\htdocs\KVN_Construction\app\views\layouts\*.php')
for file_path in files:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    if "base_url('public/" in content:
        new_content = content.replace("base_url('public/", "base_url('")
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Fixed {file_path}")
