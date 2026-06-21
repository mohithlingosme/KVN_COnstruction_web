import os
import re

dirs_to_check = [r'c:\xampp\htdocs\KVN_Construction\public\admin', r'c:\xampp\htdocs\KVN_Construction\public\client']

for root, _, files in os.walk(r'c:\xampp\htdocs\KVN_Construction\public'):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            # Only process if inside admin or client
            if 'public\\admin' in filepath or 'public\\client' in filepath:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                # Replace href="../dashboard.php" -> href="<?php echo base_url('admin/dashboard.php'); ?>" (if in admin)
                # This regex is too risky without knowing exact context. Let's do explicit replacements for common ones based on previous logs.
                
                # Let's replace common broken patterns found earlier:
                content = content.replace('href="../dashboard.php"', 'href="<?php echo base_url(\'admin/dashboard.php\'); ?>"')
                content = content.replace('href="../users/create.php"', 'href="<?php echo base_url(\'admin/users/create.php\'); ?>"')
                content = content.replace('href="../projects/create.php"', 'href="<?php echo base_url(\'admin/projects/create.php\'); ?>"')
                content = content.replace('href="users/index.php"', 'href="<?php echo base_url(\'admin/users/index.php\'); ?>"')
                content = content.replace('href="projects/index.php"', 'href="<?php echo base_url(\'admin/projects/index.php\'); ?>"')
                content = content.replace('href="blogs/index.php"', 'href="<?php echo base_url(\'admin/blogs/index.php\'); ?>"')
                content = content.replace('href="portfolio/index.php"', 'href="<?php echo base_url(\'admin/portfolio/index.php\'); ?>"')
                content = content.replace('href="quotations/index.php"', 'href="<?php echo base_url(\'admin/quotations/index.php\'); ?>"')
                content = content.replace('href="security/logs.php"', 'href="<?php echo base_url(\'admin/security/logs.php\'); ?>"')
                content = content.replace('href="leads/index.php"', 'href="<?php echo base_url(\'admin/leads/index.php\'); ?>"')
                content = content.replace('href="blogs/create.php"', 'href="<?php echo base_url(\'admin/blogs/create.php\'); ?>"')
                content = content.replace('href="../projects/index.php"', 'href="<?php echo base_url(\'admin/projects/index.php\'); ?>"')
                content = content.replace('href="../quotations/index.php"', 'href="<?php echo base_url(\'admin/quotations/index.php\'); ?>"')
                content = content.replace('href="../payments/index.php"', 'href="<?php echo base_url(\'admin/payments/index.php\'); ?>"')
                content = content.replace('href="../logout.php"', 'href="<?php echo base_url(\'logout.php\'); ?>"')
                content = content.replace('href="../support/tickets.php"', 'href="<?php echo base_url(\'admin/support/tickets.php\'); ?>"')
                content = content.replace('href="../documents/index.php"', 'href="<?php echo base_url(\'admin/documents/index.php\'); ?>"')

                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(content)
print("Admin links fixed.")
