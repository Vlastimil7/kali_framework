(function () {
  const formElement = document.querySelector("[data-contact-form]");
  if (!formElement) return;

  const dropzoneContainer = formElement.querySelector("[data-dropzone]");
  const fileInputElement = formElement.querySelector("[data-file-input]");
  const fileListWrapper = formElement.querySelector("[data-file-list-wrap]");
  const fileListElement = formElement.querySelector("[data-file-list]");

  if (!dropzoneContainer || !fileInputElement || !fileListWrapper || !fileListElement) return;

  // ====== LIMITY ======
  const MAX_TOTAL_ATTACHMENT_BYTES = 10 * 1024 * 1024;

  let selectedFilesInMemory = [];

  // ----------------------------------------------------------
  // i18n (z <script id="i18n-contact" type="application/json">)
  // ----------------------------------------------------------
  function loadContactTranslationsFromDom() {
    const translationsScriptElement = document.getElementById("i18n-contact");
    if (!translationsScriptElement) return null;

    try {
      return JSON.parse(translationsScriptElement.textContent || "{}");
    } catch (error) {
      return null;
    }
  }

  function getNestedTranslationValue(translationsObject, path) {
    const pathParts = String(path).split(".");
    let currentValue = translationsObject;

    for (const part of pathParts) {
      currentValue = currentValue ? currentValue[part] : undefined;
    }

    return currentValue;
  }

  function formatTranslationTemplate(template, variables) {
    let result = String(template ?? "");
    const variablesObject = variables || {};

    for (const key of Object.keys(variablesObject)) {
      result = result.replaceAll("{" + key + "}", String(variablesObject[key]));
    }

    return result;
  }

  const contactTranslations = loadContactTranslationsFromDom();

  function translate(path, fallbackText) {
    if (!contactTranslations) return fallbackText ?? path;

    const value = getNestedTranslationValue(contactTranslations, path);
    return value ?? (fallbackText ?? path);
  }

  // ----------------------------------------------------------
  // UI: Summary row
  // ----------------------------------------------------------
  const attachmentsSummaryRow = document.createElement("div");
  attachmentsSummaryRow.className = "mt-3 flex items-center justify-between text-xs text-gray-200";
  attachmentsSummaryRow.innerHTML = `
    <span data-files-summary></span>
    <button type="button" data-clear-files class="underline hover:text-white cursor-pointer">
      ${translate("attachments.clear_all", "Vymazat vše")}
    </button>
  `;

  fileListWrapper.parentNode.insertBefore(attachmentsSummaryRow, fileListWrapper);

  const summaryTextElement = attachmentsSummaryRow.querySelector("[data-files-summary]");
  const clearAllButton = attachmentsSummaryRow.querySelector("[data-clear-files]");

  // ----------------------------------------------------------
  // Helpers
  // ----------------------------------------------------------
  function escapeHtml(unsafeText) {
    return String(unsafeText)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function formatBytesToHumanReadable(bytes) {
    const kiloBytes = bytes / 1024;
    if (kiloBytes < 1024) return `${Math.round(kiloBytes)} KB`;
    return `${(kiloBytes / 1024).toFixed(1)} MB`;
  }

  function getTotalBytesOfFiles(filesArray) {
    return filesArray.reduce((sum, file) => sum + (file.size || 0), 0);
  }

  function showTotalSizeExceededToast(totalBytesIfAdded) {
    if (!window.toast) return;

    toast.error(translate("attachments.toast_total_exceeded_text", "Celková velikost příloh nesmí přesáhnout 10 MB."), {
      title: translate("attachments.toast_total_exceeded_title", "Přílohy"),
      timeout: 5000,
      message: formatTranslationTemplate(
        translate("attachments.toast_total_exceeded_msg", "Celkem: {total} (max {max})"),
        {
          total: formatBytesToHumanReadable(totalBytesIfAdded),
          max: formatBytesToHumanReadable(MAX_TOTAL_ATTACHMENT_BYTES),
        },
      ),
      position: "top-right",
    });
  }

  function showDuplicateFileToast(fileName) {
    if (!window.toast) return;

    toast.info(translate("attachments.toast_duplicate_text", "Soubor už je přidaný, nepřidávám ho znovu."), {
      title: translate("attachments.toast_total_exceeded_title", "Přílohy"),
      timeout: 3500,
      message: fileName
        ? formatTranslationTemplate(
            translate("attachments.toast_duplicate_msg", "Soubor: {file}"),
            { file: fileName },
          )
        : "",
      position: "top-right",
    });
  }

  function showRemovedFileToast(fileName) {
    if (!window.toast) return;

    toast.info(translate("attachments.toast_removed_text", "Soubor byl odebrán."), {
      title: translate("attachments.toast_total_exceeded_title", "Přílohy"),
      timeout: 2500,
      message: fileName
        ? formatTranslationTemplate(
            translate("attachments.toast_removed_msg", "Soubor: {file}"),
            { file: fileName },
          )
        : "",
      position: "top-right",
    });
  }

  function areFilesSame(firstFile, secondFile) {
    return (
      firstFile.name === secondFile.name &&
      firstFile.size === secondFile.size &&
      firstFile.lastModified === secondFile.lastModified
    );
  }

  function isDuplicateFile(newFile) {
    return selectedFilesInMemory.some((existingFile) => areFilesSame(existingFile, newFile));
  }

  function syncFileInputWithSelectedFiles() {
    const dataTransfer = new DataTransfer();

    selectedFilesInMemory.forEach((file) => {
      dataTransfer.items.add(file);
    });

    fileInputElement.files = dataTransfer.files;
  }

  function updateSummaryRow() {
    const filesCount = selectedFilesInMemory.length;
    const totalBytes = getTotalBytesOfFiles(selectedFilesInMemory);

    if (filesCount === 0) {
      summaryTextElement.textContent = "";
      clearAllButton.classList.add("hidden");
      return;
    }

    summaryTextElement.textContent = formatTranslationTemplate(
      translate("attachments.summary", "Přílohy: {count} • Celkem: {total} / {max}"),
      {
        count: filesCount,
        total: formatBytesToHumanReadable(totalBytes),
        max: formatBytesToHumanReadable(MAX_TOTAL_ATTACHMENT_BYTES),
      },
    );

    clearAllButton.classList.remove("hidden");
  }

  function renderFileList() {
    fileListElement.innerHTML = "";

    if (selectedFilesInMemory.length === 0) {
      fileListWrapper.classList.add("hidden");
      updateSummaryRow();
      return;
    }

    fileListWrapper.classList.remove("hidden");

    selectedFilesInMemory.forEach((file, index) => {
      const listItem = document.createElement("li");
      listItem.className = "flex items-center justify-between gap-3";

      const removeButtonText = translate("attachments.remove", "Odebrat");
      const removeAriaLabel = formatTranslationTemplate(
        translate("attachments.remove_aria", "Odebrat soubor {file}"),
        { file: file.name },
      );

      listItem.innerHTML = `
        <div class="min-w-0 flex items-center gap-3">
          <span class="break-all whitespace-normal leading-snug">
            ${escapeHtml(file.name)}
          </span>
        </div>

        <div class="shrink-0 flex items-center gap-3">
          <span class="text-xs text-white">${formatBytesToHumanReadable(file.size)}</span>

          <button
            type="button"
            data-remove-file-index="${index}"
            class="text-xs underline hover:text-white cursor-pointer"
            aria-label="${escapeHtml(removeAriaLabel)}"
            title="${escapeHtml(removeButtonText)}"
          >
            ${escapeHtml(removeButtonText)}
          </button>
        </div>
      `;

      fileListElement.appendChild(listItem);
    });

    updateSummaryRow();
  }

  function tryAddFilesToSelection(fileList) {
    const incomingFiles = Array.from(fileList || []);
    if (incomingFiles.length === 0) return;

    for (const file of incomingFiles) {
      if (isDuplicateFile(file)) {
        showDuplicateFileToast(file.name);
        continue;
      }

      const totalBytesIfAdded = getTotalBytesOfFiles([...selectedFilesInMemory, file]);
      if (totalBytesIfAdded > MAX_TOTAL_ATTACHMENT_BYTES) {
        showTotalSizeExceededToast(totalBytesIfAdded);
        break;
      }

      selectedFilesInMemory.push(file);
    }

    syncFileInputWithSelectedFiles();
    renderFileList();
  }

  function clearAllFiles() {
    selectedFilesInMemory = [];
    fileInputElement.value = "";
    renderFileList();
  }

  // ----------------------------------------------------------
  // Events
  // ----------------------------------------------------------
  dropzoneContainer.addEventListener("click", () => fileInputElement.click());

  fileInputElement.addEventListener("change", () => {
    tryAddFilesToSelection(fileInputElement.files);
  });

  clearAllButton.addEventListener("click", () => {
    clearAllFiles();
  });

  fileListElement.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-remove-file-index]");
    if (!removeButton) return;

    const indexString = removeButton.getAttribute("data-remove-file-index");
    const fileIndex = Number(indexString);
    if (!Number.isFinite(fileIndex)) return;

    const removedFile = selectedFilesInMemory[fileIndex];
    selectedFilesInMemory.splice(fileIndex, 1);

    syncFileInputWithSelectedFiles();
    renderFileList();

    if (removedFile && removedFile.name) {
      showRemovedFileToast(removedFile.name);
    }
  });

  dropzoneContainer.addEventListener("dragover", (event) => {
    event.preventDefault();
    dropzoneContainer.classList.add("border-[var(--color-red)]/60", "bg-white");
  });

  dropzoneContainer.addEventListener("dragleave", () => {
    dropzoneContainer.classList.remove("border-[var(--color-red)]/60", "bg-white");
  });

  dropzoneContainer.addEventListener("drop", (event) => {
    event.preventDefault();
    dropzoneContainer.classList.remove("border-[var(--color-red)]/60", "bg-white");

    const droppedFiles = event.dataTransfer ? event.dataTransfer.files : null;
    if (!droppedFiles || droppedFiles.length === 0) return;

    tryAddFilesToSelection(droppedFiles);
  });

  formElement.__getSelectedAttachmentFiles = function () {
    return selectedFilesInMemory.slice();
  };

  // init
  clearAllButton.classList.add("hidden");
  renderFileList();
})();
