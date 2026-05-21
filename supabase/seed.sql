insert into public.services (slug, title, summary, description)
values
  ('residential-construction', 'Residential Construction', 'Luxury homes and villas', 'End-to-end construction delivery for premium homes.'),
  ('commercial-construction', 'Commercial Construction', 'Office and business spaces', 'Program-managed commercial build execution.'),
  ('interior-fitouts', 'Interior Fit-Outs', 'Bespoke interiors and smart homes', 'Premium interior design and execution.'),
  ('documentation-services', 'Documentation Services', 'Approvals and permits', 'Construction documentation support.');

insert into public.blog_categories (name, slug)
values
  ('Construction tips', 'construction-tips'),
  ('Budget estimation', 'budget-estimation'),
  ('Material explanation', 'material-explanation'),
  ('Legal information', 'legal-information')
on conflict do nothing;

insert into public.packages (slug, title, price_per_sqft, blurb, features, target_audience)
values
  (
    'smart-build',
    'Smart Build',
    1900,
    'Efficient package for dependable delivery and cost control.',
    '["RCC structure", "Dedicated site engineer", "Bi-weekly reports"]'::jsonb,
    'First home builds'
  ),
  (
    'premium-turnkey',
    'Premium Turnkey',
    2350,
    'Balanced premium package with stronger controls and finishes.',
    '["Premium materials", "Weekly updates", "Client portal"]'::jsonb,
    'High-quality family homes'
  ),
  (
    'signature-luxe',
    'Signature Luxe',
    2950,
    'Luxury delivery with executive project control.',
    '["Concierge PM", "Luxury finishes", "Smart home integration"]'::jsonb,
    'Luxury villas'
  )
on conflict do nothing;

insert into public.faqs (category, question, answer, sort_order)
values
  ('Construction', 'How is pricing calculated?', 'Pricing depends on scope, engineering, finish level, and site constraints.', 1),
  ('Timeline', 'How often are updates shared?', 'Weekly updates are standard for active projects.', 2),
  ('Documentation', 'Can you help with approvals?', 'Yes, documentation and permit support are part of the service stack.', 3)
on conflict do nothing;
