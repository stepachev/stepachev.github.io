---
layout: layouts/base.html
title: "Проекты"
---

<div class="projects-grid">
  {%- for project in collections.projects | sort(attribute="data.order") -%}
    <a href="{{ project.url }}" class="project-card">
      <img src="{{ project.data.image }}" alt="{{ project.data.title }}">
      <div class="project-info">
        <h3>{{ project.data.title }}</h3>

      </div>
    </a>
  {%- endfor -%}
</div>
