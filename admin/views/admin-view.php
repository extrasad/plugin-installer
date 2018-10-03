<div class="wrap">

  <h1>
    <?= esc_html(get_admin_page_title()); ?>
  </h1>

  <div class="pinst__main-panel">
    <div class="pinst__first-panel">
      <h3>Plugins Instalados</h3>
      <div class="pinst__plugins">
        <ul>
        </ul>
        <div class="showbox showbox--overlay">
          <div class="loader loader--overlay">
            <svg class="circular" viewBox="25 25 50 50">
              <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="pinst__second-panel">
      <h3>Instalar plugin desde repositorio WP</h3>
    
      <div id="url-request-container" class="pinst__input-group">
        <label class="pinst__label" for="url-request">From Url</label>
        <input type="text" id="url-request" class="pinst__input">
      </div>
    
      <h3>Or</h3>
    
      <div class="pinst__input-group pinst__item">
        <button type="button" id="download-button" class="pinst__button button button-secondary">Download</button>
      </div>
    
      <button type="button" id="install-button" class="pinst__button button button-primary">Install Plugins</button>
      <a class="pinst__link" href="https://www.thinkdifferent.es/plugins-permitidos">Ver lista de plugins a solicitar instalación nueva</a>
    </div>
  </div>

  <div class="pinst__report">

  </div>
</div> 