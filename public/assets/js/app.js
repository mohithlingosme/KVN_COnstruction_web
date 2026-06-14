// Global JS for homepage and shared UI

function calculateCost() {
  const plotLength = Number(document.getElementById('plotLength')?.value || 0);
  const plotWidth = Number(document.getElementById('plotWidth')?.value || 0);
  const sqft = plotLength * plotWidth;

  const sqftInput = document.getElementById('sqft');
  if (sqftInput) sqftInput.value = sqft;
  const sqftValue = document.getElementById('sqftValue');
  if (sqftValue) sqftValue.textContent = sqft + ' sqft';

  const floors = Number(document.getElementById('floors')?.value || 1);
  const qualitySelect = document.getElementById('quality');
  const selectedOption = qualitySelect?.selectedOptions?.[0];

  const basePrice = Number(selectedOption?.dataset?.price || 0);
  const timeline = selectedOption?.dataset?.timeline || '--';
  const material = selectedOption?.dataset?.material || '--';
  const packageName = qualitySelect?.value || '--';

  const totalBuiltup = sqft * floors;
  const totalCost = totalBuiltup * basePrice;

  const totalCostEl = document.getElementById('totalCost');
  if (totalCostEl) totalCostEl.textContent = '₹' + (isFinite(totalCost) ? Math.round(totalCost).toLocaleString('en-IN') : '0');

  const builtupAreaEl = document.getElementById('builtupArea');
  if (builtupAreaEl) builtupAreaEl.textContent = totalBuiltup + ' sqft';

  const timelineEl = document.getElementById('timeline');
  if (timelineEl) timelineEl.textContent = timeline;

  const packageEl = document.getElementById('package');
  if (packageEl) packageEl.textContent = selectedOption?.text || packageName;

  const materialEl = document.getElementById('materialGrade');
  if (materialEl) materialEl.textContent = material;
}

// Keep global namespace minimal
window.calculateCost = calculateCost;

