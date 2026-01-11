---
layout: layouts/base.html
title: "Главная"
---

<div class="projects-scroll">
  <div class="projects-track">
    {%- for project in collections.projects | sort(attribute="data.order") -%}
      <a href="{{ project.url }}" class="project-item">
        <img src="{{ project.data.image }}" alt="{{ project.data.title }}">
        <div class="project-title">{{ project.data.title }}</div>
      </a>
    {%- endfor -%}
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.projects-track');
    if (!track) return;

    // Дублируем контент для бесшовного скролла (делаем это несколько раз, чтобы точно хватило ширины)
    // Важно: сначала клонируем все элементы
    const originalContent = track.innerHTML; 
    track.innerHTML += originalContent + originalContent; // Тройное дублирование для надежности

    let position = 0;
    let isDragging = false;
    let startX = 0;
    let oldTranslate = 0;
    let requestID;
    
    // Скорость автоматической прокрутки (пикселей за кадр)
    const baseSpeed = 0.5;
    let speed = baseSpeed;

    // Ширина одного набора элементов. 
    // Поскольку мы утроили контент, полная ширина = 3 * singleWidth.
    // Нам нужно сбрасывать позицию, когда прокрутили 1/3.
    // Вычислим ширину после рендеринга.
    let singleSetWidth = 0;

    function calculateWidth() {
       // Примерно 1/3 от общей ширины трека
       singleSetWidth = track.scrollWidth / 3;
    }
    
    // Ждем загрузки картинок для точного расчета ширины
    window.addEventListener('load', calculateWidth);
    window.addEventListener('resize', calculateWidth);
    // Вызовем сразу на всякий случай
    calculateWidth();

    function animate() {
      if (!isDragging) {
        position -= speed;
        
        // Бесшовный сброс
        // Если ушли слишком далеко влево (-singleSetWidth), прыгаем в 0
        if (position <= -singleSetWidth) {
          position += singleSetWidth;
        }
        // Если утащили слишком далеко вправо (больше 0), прыгаем в -singleSetWidth
        if (position > 0) {
          position -= singleSetWidth;
        }
      }

      track.style.transform = `translateX(${position}px)`;
      requestID = requestAnimationFrame(animate);
    }

    // Запускаем анимацию
    animate();

    // --- Drag Logic ---

    track.addEventListener('mousedown', startDrag);
    track.addEventListener('touchstart', startDrag, {passive: true});

    track.addEventListener('mousemove', moveDrag);
    track.addEventListener('touchmove', moveDrag, {passive: true});

    track.addEventListener('mouseup', endDrag);
    track.addEventListener('mouseleave', endDrag);
    track.addEventListener('touchend', endDrag);

    // Пауза при наведении (как было раньше)
    track.addEventListener('mouseenter', () => {
      if (!isDragging) speed = 0;
    });
    
    track.addEventListener('mouseleave', () => {
      if (!isDragging) speed = baseSpeed;
    });

    function startDrag(e) {
      isDragging = true;
      startX = getPositionX(e);
      oldTranslate = position;
      track.style.cursor = 'grabbing';
      // Отменяем стандартное поведение перетаскивания картинок
      e.preventDefault(); 
    }

    function moveDrag(e) {
      if (!isDragging) return;
      
      const currentX = getPositionX(e);
      const diff = currentX - startX;
      position = oldTranslate + diff;
    }

    function endDrag() {
      if(!isDragging) return;
      isDragging = false;
      track.style.cursor = 'grab';
      // Если мышь все еще над элементом, скорость остается 0, иначе восстанавливается
      // Но внутри обработчика mouseleave мы это уже учли
    }

    function getPositionX(e) {
      return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }
    
    // Предотвращаем клик по ссылке, если был драг (опционально, но полезно)
    track.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', (e) => {
        if (Math.abs(position - oldTranslate) > 5) { // Если сдвинули хоть немного
          e.preventDefault(); // Это не клик, а драг
        }
      });
    });
  });
</script>

<h2 style="margin-top: 80px; margin-bottom: 24px;">Последние записи</h2>

<ul class="post-list">
{% for post in collections.posts | reverse %}
  <li>
    <a href="{{ post.url }}">{{ post.data.title }}</a>
  </li>
{% endfor %}
</ul>
