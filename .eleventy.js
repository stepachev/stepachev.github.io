const { DateTime } = require("luxon");

module.exports = function(eleventyConfig) {
  // Копируем папку с CSS в итоговую сборку
  eleventyConfig.addPassthroughCopy("src/css");
  // Копируем папку с изображениями
  eleventyConfig.addPassthroughCopy("src/images");


  eleventyConfig.addFilter("dateIso", (dateObj) => {
    return DateTime.fromJSDate(dateObj).toISODate();
  });

  eleventyConfig.addFilter("dateReadable", (dateObj) => {
    return DateTime.fromJSDate(dateObj).toFormat("dd.MM.yyyy");
  });

  // Добавляем коллекцию проектов
  eleventyConfig.addCollection("projects", function(collectionApi) {
    return collectionApi.getFilteredByGlob("src/projects/*.md");
  });

  return {
    markdownTemplateEngine: "njk",
    htmlTemplateEngine: "njk",
    dir: {
      input: "src",
      output: "_site",
      includes: "_includes"
    }
  };
};
