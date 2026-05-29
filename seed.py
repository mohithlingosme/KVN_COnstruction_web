#!/usr/bin/env python3
import os
import sys

try:
    import mysql.connector
except ImportError:
    os.system('pip install mysql-connector-python bcrypt 2>nul')
    import mysql.connector

DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': '',
    'database': 'kvnc_platform'
}

def get_connection():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except Exception as e:
        print(f'Database connection failed: {e}')
        sys.exit(1)

def seed_all(conn):
    cur = conn.cursor()
    print('Seeding data...')
    roles = [('admin','Administrator'),('client','Client'),('employee','Employee')]
    cur.executemany('INSERT IGNORE INTO roles (role_key,role_name) VALUES (%s,%s)', roles)
    
    perms = [('dashboard.view','View Dashboard'),('leads.view','View Leads'),('leads.create','Create Leads'),
             ('leads.edit','Edit Leads'),('leads.delete','Delete Leads'),('projects.view','View Projects'),
             ('projects.create','Create Projects'),('projects.edit','Edit Projects'),
             ('quotations.view','View Quotations'),('quotations.create','Create Quotations'),
             ('cms.view','View CMS'),('cms.edit','Edit CMS'),('media.view','View Media'),
             ('media.upload','Upload Media'),('users.view','View Users'),('users.edit','Edit Users'),
             ('settings.edit','Edit Settings'),('reports.view','View Reports'),('security.view','View Security Logs')]
    cur.executemany('INSERT IGNORE INTO permissions (permission_key,permission_name) VALUES (%s,%s)', perms)
    
    statuses = [(1,'New','#2196F3',1),(2,'Contacted','#FF9800',2),(3,'Qualified','#9C27B0',3),
                (4,'Proposal Sent','#00BCD4',4),(5,'Negotiation','#FFC107',5),(6,'Won','#4CAF50',6),(7,'Lost','#F44336',7)]
    cur.executemany('INSERT IGNORE INTO lead_statuses (id,name,color,sort_order) VALUES (%s,%s,%s,%s)', statuses)
    
    pstatus = [(1,'Planning'),(2,'Foundation'),(3,'Structure'),(4,'Finishing'),(5,'Completed'),(6,'On Hold')]
    cur.executemany('INSERT IGNORE INTO project_statuses (id,name) VALUES (%s,%s)', pstatus)
    
    settings = [('company_name','KVN Construction'),('company_tagline','Building Dreams, Delivering Excellence'),
                ('company_phone','+91 9876543210'),('company_email','info@kvnconstruction.com'),
                ('currency','INR'),('gst_percentage','18')]
    cur.executemany('INSERT IGNORE INTO site_settings (setting_key,setting_value) VALUES (%s,%s)', settings)
    
    admin_hash = '.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    cur.execute('INSERT IGNORE INTO users (full_name,email,phone,password,role,status,email_verified_at,created_at) VALUES (%s,%s,%s,%s,%s,%s,NOW(),NOW())',
                ('Admin User','admin@kvnconstruction.com','+919876543210',admin_hash,'admin','active'))
    
    zones = [('Premium Areas',1.20),('Urban Areas',1.10),('Suburban Areas',1.00),('Semi-Urban Areas',0.95),('Rural Areas',0.90),('Industrial Areas',1.15)]
    cur.executemany('INSERT IGNORE INTO location_zones (zone_name,multiplier) VALUES (%s,%s)', zones)
    
    packages = [('Basic Package','basic-package','Essential construction services.',500000,1500,1,8,'active'),
                ('Standard Package','standard-package','Comprehensive construction.',800000,1800,1,10,'active'),
                ('Premium Package','premium-package','Luxury construction.',1200000,2200,1,12,'active')]
    cur.executemany('INSERT IGNORE INTO construction_packages (package_name,slug,description,base_price,price_per_sqft,includes_gst,delivery_time_months,status) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)', packages)
    
    features = [(1,'Structural Design'),(1,'Basic Materials'),(1,'Standard Flooring'),(1,'Basic Plumbing'),(1,'Standard Electrical'),
                (2,'Structural Design'),(2,'Premium Materials'),(2,'Vitrified Flooring'),(2,'Premium Plumbing'),(2,'Modular Electrical'),(2,'Painting'),(2,'Fixtures'),
                (3,'Structural Design'),(3,'Luxury Materials'),(3,'Italian Flooring'),(3,'Smart Plumbing'),(3,'Smart Electrical'),(3,'Premium Fixtures'),(3,'Smart Home Integration')]
    cur.executemany('INSERT IGNORE INTO package_features (package_id,feature_name) VALUES (%s,%s)', features)
    
    services = [('Residential Construction','residential-construction','Complete home construction services.','bi bi-house-door','Expert residential construction',1,'active'),
                ('Architectural Design','architectural-design','Professional architectural services.','bi bi-pen','Creative designs',1,'active'),
                ('Commercial Construction','commercial-construction','Commercial spaces.','bi bi-building','Reliable solutions',0,'active'),
                ('Interior Design','interior-design','Transform your spaces.','bi bi-lamp','Elegant design',0,'active'),
                ('Renovation Services','renovation-services','Upgrade and modernize.','bi bi-tools','Renovation',0,'active'),
                ('Project Management','project-management','End-to-end management.','bi bi-clipboard-check','Professional oversight',1,'active')]
    cur.executemany('INSERT IGNORE INTO services (service_name,slug,description,icon,short_description,featured,status) VALUES (%s,%s,%s,%s,%s,%s,%s)', services)
    
    faqs = [('What is the typical timeline?','8-12 months depending on size.','Construction',1,'active'),
            ('Do you provide free estimates?','Yes, free consultations available.','Estimates',2,'active'),
            ('What payment options?','Flexible payment schedules.','Payment',3,'active'),
            ('Do you handle vastu compliance?','Yes, all designs ensure vastu compliance.','Design',4,'active'),
            ('Do you provide warranties?','Yes, comprehensive warranties.','Warranty',5,'active'),
            ('Can I customize my home design?','Absolutely! We encourage customization.','Design',6,'active')]
    cur.executemany('INSERT IGNORE INTO faqs (question,answer,category,sort_order,status) VALUES (%s,%s,%s,%s,%s)', faqs)
    
    testimonials = [('Rajesh Kumar','Whitefield, Bangalore','KVN delivered our dream home on time.',5,'Premium Residential','active'),
                    ('Priya Sharma','HSR Layout, Bangalore','From design to handover, KVN exceeded expectations.',5,'Standard Residential','active'),
                    ('Anand Reddy','Electronic City, Bangalore','Beautiful home within budget.',5,'Basic Residential','active'),
                    ('Meera Nair','Marathahalli, Bangalore','Professional, reliable, quality-focused.',5,'Premium Residential','active')]
    cur.executemany('INSERT IGNORE INTO testimonials (client_name,client_location,review,rating,project_type,status) VALUES (%s,%s,%s,%s,%s,%s)', testimonials)
    
    advantages = [('Expert Architecture','Dedicated team of experts.','bi bi-building',1,'active'),
                  ('Precision Design','Structural engineers ensure compliance.','bi bi-check-circle',2,'active'),
                  ('Construction Oversight','Dedicated site engineer.','bi bi-person-workspace',3,'active'),
                  ('Key Handover','Timely completion guaranteed.','bi bi-house-check',4,'active')]
    cur.executemany('INSERT IGNORE INTO about_advantages (title,description,icon,sort_order,status) VALUES (%s,%s,%s,%s,%s)', advantages)
    
    steps = [('Stage 1 - Client Requirements','Understanding requirements.',1,'active'),
             ('Stage 2 - Design Specifications','Conceptual plans created.',2,'active'),
             ('Stage 3 - Transparent Agreement','Project costing finalized.',3,'active'),
             ('Stage 4 - Construction Execution','Site engineer oversight.',4,'active'),
             ('Stage 5 - Quality Checks','Milestone checks.',5,'active'),
             ('Stage 6 - Final Handover','Keys handed over.',6,'active')]
    cur.executemany('INSERT IGNORE INTO about_process_steps (step_title,step_description,sort_order,status) VALUES (%s,%s,%s,%s)', steps)
    
    specs = [('Years of Experience','15+',1,'active'),('Projects Completed','200+',2,'active'),
             ('Happy Clients','180+',3,'active'),('Team Members','50+',4,'active'),
             ('Areas Served','25+',5,'active'),('Customer Rating','4.8/5',6,'active')]
    cur.executemany('INSERT IGNORE INTO about_specifications (spec_title,spec_value,sort_order,status) VALUES (%s,%s,%s,%s)', specs)
    
    conn.commit()
    print('Seeding complete!')

def main():
    print('KVN Construction Database Seeder')
    print('=================================')
    conn = get_connection()
    try:
        seed_all(conn)
    finally:
        conn.close()

if __name__ == '__main__':
    main()
