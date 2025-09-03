function addPair() {
  const container = document.getElementById("subjectTeacherPairs");
  const tpl = document.getElementById("subjectTeacherTemplate");
  if (!tpl) return;
  const clone = tpl.content.firstElementChild.cloneNode(true);
  container.appendChild(clone);
}

function removePair(btn) {
  const row = btn.closest(".flex");
  if (row) row.remove();
}
