TYPE=VIEW
query=select `b`.`id` AS `id`,`b`.`title` AS `title`,`b`.`name` AS `name`,`b`.`slug` AS `slug`,`b`.`category_id` AS `category_id`,`b`.`category` AS `category`,`b`.`excerpt` AS `excerpt`,`b`.`content` AS `content`,`b`.`featured_image` AS `featured_image`,`b`.`meta_title` AS `meta_title`,`b`.`meta_description` AS `meta_description`,`b`.`tags` AS `tags`,`b`.`status` AS `status`,`b`.`is_featured` AS `is_featured`,`b`.`author_id` AS `author_id`,`b`.`views_count` AS `views_count`,`b`.`published_at` AS `published_at`,`b`.`created_at` AS `created_at`,`b`.`updated_at` AS `updated_at`,`b`.`deleted_at` AS `deleted_at`,`bc`.`category_name` AS `category_name` from (`kvnc_platform`.`blogs` `b` left join `kvnc_platform`.`blog_categories` `bc` on(`bc`.`id` = `b`.`category_id`))
md5=d0b2cfa58c72cc647d666f83d7bba65b
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001785844015550840
create-version=2
source=SELECT b.*, bc.category_name\n  FROM blogs b\n  LEFT JOIN blog_categories bc ON bc.id = b.category_id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_general_ci
view_body_utf8=select `b`.`id` AS `id`,`b`.`title` AS `title`,`b`.`name` AS `name`,`b`.`slug` AS `slug`,`b`.`category_id` AS `category_id`,`b`.`category` AS `category`,`b`.`excerpt` AS `excerpt`,`b`.`content` AS `content`,`b`.`featured_image` AS `featured_image`,`b`.`meta_title` AS `meta_title`,`b`.`meta_description` AS `meta_description`,`b`.`tags` AS `tags`,`b`.`status` AS `status`,`b`.`is_featured` AS `is_featured`,`b`.`author_id` AS `author_id`,`b`.`views_count` AS `views_count`,`b`.`published_at` AS `published_at`,`b`.`created_at` AS `created_at`,`b`.`updated_at` AS `updated_at`,`b`.`deleted_at` AS `deleted_at`,`bc`.`category_name` AS `category_name` from (`kvnc_platform`.`blogs` `b` left join `kvnc_platform`.`blog_categories` `bc` on(`bc`.`id` = `b`.`category_id`))
mariadb-version=100432
