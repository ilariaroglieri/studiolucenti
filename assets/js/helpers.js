// split text in lines
function splitLines(el) {

  const words = el.innerText.split(" ");
  el.innerHTML = words.map(word => `<span class="word">${word}</span>`).join(" ");

  const wordEls = el.querySelectorAll('.word');

  const lines = [];
  let currentLine = [];
  let currentTop = null;

  wordEls.forEach((word) => {
    const top = word.offsetTop;

    if (currentTop === null) currentTop = top;

    if (Math.abs(top - currentTop) > 5) {
      lines.push(currentLine);
      currentLine = [];
      currentTop = top;
    }

    currentLine.push(word);
  });

  if (currentLine.length) lines.push(currentLine);

  // ricostruisci HTML
  el.innerHTML = lines.map(line => {
    const lineContent = line.map(w => w.innerText).join(" ");
    return `
      <span class="line">
        <span class="line-inner">${lineContent}</span>
      </span>
    `;
  }).join("");
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