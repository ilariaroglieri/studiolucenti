// split text in lines
function splitLines(element) {
  const words = element.innerText.split(" ");
  element.innerHTML = "";

  const lines = [];
  let currentLine = "";

  words.forEach((word) => {
    const testLine = currentLine + word + " ";
    element.innerHTML = `<span>${testLine}</span>`;

    const span = element.querySelector("span");

    if (span.offsetHeight > span.scrollHeight) {
      lines.push(currentLine);
      currentLine = word + " ";
    } else {
      currentLine = testLine;
    }
  });

  lines.push(currentLine);

  element.innerHTML = lines
    .map(line => `
      <span class="line">
        <span class="line-inner">${line}</span>
      </span>
    `)
    .join("");
}

// group projects into rows to animate them sequentially
function groupByRows(elements) {
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