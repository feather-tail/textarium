import { textarea } from "./core.js";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".article-form");
  if (!textarea || !form) return;

  textarea.addEventListener("dragover", (e) => e.preventDefault());

  textarea.addEventListener("drop", async (e) => {
    e.preventDefault();

    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith("image/")) {
      alert("Можно загружать только изображения");
      return;
    }

    const formData = new FormData();
    formData.append("file", file);

    try {
      const response = await fetch("/api/upload-image", {
        method: "POST",
        headers: {
          "X-CSRF-Token": document.querySelector("#csrf-token")?.value || "",
        },
        body: formData,
      });

      const text = await response.text();
      console.log("[upload response]", text);

      let json;
      try {
        json = JSON.parse(text);
      } catch (err) {
        throw new Error("Некорректный ответ сервера (не JSON)");
      }

      const url = json.data?.attributes?.url;
      if (json.success && url) {
        const tag = `[img]${url}[/img]`;
        const pos = textarea.selectionStart;
        textarea.setRangeText(tag, pos, pos, "end");
        textarea.focus();
      } else {
        alert(json.message || "Ошибка загрузки изображения");
      }
    } catch (err) {
      console.warn("Ошибка dnd загрузки:", err);
      alert("Ошибка загрузки: " + err.message);
    }
  });
});
