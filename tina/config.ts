import { defineConfig } from "tinacms";

// Your hosting provider likely exposes this as an environment variable
const branch = process.env.HEAD || process.env.VERCEL_GIT_COMMIT_REF || "main";

export default defineConfig({
    branch,
    clientId: null, // Get this from tina.io
    token: null, // Get this from tina.io

    build: {
        outputFolder: "admin",
        publicFolder: "_site",
    },
    media: {
        tina: {
            mediaRoot: "images",
            publicFolder: "src",
        },
    },
    schema: {
        collections: [
            {
                name: "post",
                label: "Блог",
                path: "src/posts",
                defaultItem: () => {
                    return {
                        layout: "layouts/post.html",
                        tags: "posts",
                        date: new Date().toISOString(),
                    }
                },
                fields: [
                    {
                        type: "string",
                        name: "title",
                        label: "Заголовок",
                        isTitle: true,
                        required: true,
                    },
                    {
                        type: "datetime",
                        name: "date",
                        label: "Дата",
                        required: true,
                    },
                    {
                        type: "image",
                        name: "image",
                        label: "Обложка (необязательно)",
                    },
                    {
                        type: "string",
                        name: "layout",
                        label: "Layout",
                        required: true,
                        ui: {
                            component: 'hidden' // Скрываем поле layout, чтобы не мешало
                        }
                    },
                    {
                        type: "string",
                        name: "tags",
                        label: "Tags",
                        ui: {
                            component: 'hidden'
                        }
                    },
                    {
                        type: "rich-text",
                        name: "body",
                        label: "Контент",
                        isBody: true,
                    },
                ],
            },
            {
                name: "project",
                label: "Проекты",
                path: "src/projects",
                defaultItem: () => {
                    return {
                        layout: "layouts/project.html",
                        tags: "projects",
                        order: 0,
                    }
                },
                fields: [
                    {
                        type: "string",
                        name: "title",
                        label: "Название проекта",
                        isTitle: true,
                        required: true,
                    },
                    {
                        type: "image",
                        name: "image",
                        label: "Главное изображение",
                        required: true,
                    },
                    {
                        type: "string",
                        name: "description",
                        label: "Краткое описание",
                    },
                    {
                        type: "number",
                        name: "year",
                        label: "Год",
                    },
                    {
                        type: "number",
                        name: "order",
                        label: "Порядок сортировки",
                    },
                    {
                        type: "string",
                        name: "layout",
                        label: "Layout",
                        required: true,
                        ui: {
                            component: 'hidden'
                        }
                    },
                    {
                        type: "string",
                        name: "tags",
                        label: "Tags",
                        ui: {
                            component: 'hidden'
                        }
                    },
                    {
                        type: "rich-text",
                        name: "body",
                        label: "Описание проекта (Контент)",
                        isBody: true,
                    },
                ],
            },
        ],
    },
});
