

export function groupByRows(elements) {
  const rows = [];

  elements.forEach((el) => {
    const top = el.offsetTop;
    let row = rows.find(r => Math.abs(r.top - top) < 5);

    if (!row) {
      row = { top, items: [] };
      rows.push(row);
    }
    row.items.push(el);
  });

  return rows;
}
