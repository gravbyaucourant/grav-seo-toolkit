document.addEventListener("DOMContentLoaded", function () {
  const keywordInput = document.querySelector(
    '[name="data[header][seo][keyword]"]'
  );
  const titleInput = document.querySelector(
    '[name="data[header][seo][title]"]'
  );
  const descInput = document.querySelector(
    '[name="data[header][seo][description]"]'
  );

  function insertMessageEl(input) {
    let msgEl = input?.previousElementSibling?.classList?.contains("seo-msg")
      ? input.previousElementSibling
      : null;

    if (!msgEl && input?.parentNode) {
      msgEl = document.createElement("div");
      msgEl.className = "seo-msg";
      msgEl.style.fontSize = "0.85em";
      msgEl.style.marginBottom = "4px";
      msgEl.style.color = "#dc3545"; // default red
      input.parentNode.insertBefore(msgEl, input);
    }

    return msgEl;
  }

  function validateKeywordPresence() {
    const keyword = keywordInput?.value?.trim()?.toLowerCase();
    const title = titleInput?.value?.toLowerCase();
    const desc = descInput?.value?.toLowerCase();

    if (!keyword) return;

    // Title Validation
    const titleMsg = insertMessageEl(titleInput);
    if (title.includes(keyword)) {
      titleInput.classList.add("seo-valid");
      titleInput.classList.remove("seo-invalid");
      titleMsg.textContent =
        "Good! You have used the focus keyword in the SEO Title.";
      titleMsg.style.color = "#28a745";
    } else {
      titleInput.classList.add("seo-invalid");
      titleInput.classList.remove("seo-valid");
      titleMsg.textContent = "Kindly use the focus keyword in the SEO Title.";
      titleMsg.style.color = "#dc3545";
    }

    // Description Validation
    const descMsg = insertMessageEl(descInput);
    if (desc.includes(keyword)) {
      descInput.classList.add("seo-valid");
      descInput.classList.remove("seo-invalid");
      descMsg.textContent =
        "Good! You have used the focus keyword in the Meta Description.";
      descMsg.style.color = "#28a745";
    } else {
      descInput.classList.add("seo-invalid");
      descInput.classList.remove("seo-valid");
      descMsg.textContent =
        "Kindly use the focus keyword in the Meta Description.";
      descMsg.style.color = "#dc3545";
    }
  }

  // Attach listeners
  [keywordInput, titleInput, descInput].forEach((input) => {
    if (input) {
      input.addEventListener("input", validateKeywordPresence);
    }
  });

  validateKeywordPresence(); // run on page load
});
document.addEventListener("DOMContentLoaded", function () {
  const keywordInput = document.querySelector(
    '[name="data[header][seo][keyword]"]'
  );
  const titleInput = document.querySelector(
    '[name="data[header][seo][title]"]'
  );
  const descInput = document.querySelector(
    '[name="data[header][seo][description]"]'
  );
  const snippetTextarea = document.querySelector(
    '[name="data[header][seo][snippet]"]'
  );

  // Create a styled preview block to replace the textarea
  const previewBlock = document.createElement("div");
  previewBlock.className = "seo-snippet-preview";
  // previewBlock.innerHTML = `
  //       <div class="snippet-title">Page Title Here</div>
  //       <div class="snippet-url">www.example.com/page-url</div>
  //       <div class="snippet-desc">Page meta description here.</div>
  //   `;
  const title = titleInput?.value?.trim() || "My title";
  const desc = descInput?.value?.trim() || "Page DescriptionS";
  const url = window.location.origin;
  // console.log("URL:", url);

  previewBlock.innerHTML = `
  <div class="snippet-url">${url}</div>
  <div class="snippet-title">${title}</div>
  <div class="snippet-desc">${desc}</div>
`;

  if (snippetTextarea?.parentNode) {
    snippetTextarea.style.display = "none"; // hide original
    snippetTextarea.parentNode.appendChild(previewBlock);
  }

  function updateSnippetPreview() {
    const title = titleInput?.value?.trim() || "Page Title";
    const desc = descInput?.value?.trim() || "Meta description goes here...";

    previewBlock.querySelector(".snippet-title").textContent = title;
    previewBlock.querySelector(".snippet-desc").textContent = desc;
  }

  [titleInput, descInput].forEach((input) => {
    if (input) input.addEventListener("input", updateSnippetPreview);
  });

  updateSnippetPreview();
});