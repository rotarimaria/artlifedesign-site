/* =========================================================
   ART LIFE DESIGN — BOTPRESS WEBCHAT FINAL V4
   ---------------------------------------------------------
   Comportament:
   - intrare nouă în tab = conversație nouă
   - refresh / reload = conversație nouă
   - navigare index.html <-> lucrari.html în același tab = păstrează conversația
   - închiderea tab-ului + revenire = conversație nouă
   - Botpress este inițializat O SINGURĂ DATĂ
   - restartConversation() NU se apelează în webchat:ready
   - resetarea se face numai DUPĂ deschiderea efectivă a chatului
   ========================================================= */

(() => {
  const CLIENT_ID =
    "5f16efb4-a4db-46f0-a01b-df2f36f2f4a7";

  const BOT_ID =
    "133db566-e14b-4bfb-bf62-c19460ad72d7";

  const TEASER_DELAY = 3200;

  const SITE_SESSION_KEY =
    "artlife_chat_site_session_v4";

  /*
    Botpress ne-a arătat în Console:
    "restartConversation called before webchat component mounted".

    De aceea:
    1. deschidem Webchat;
    2. așteptăm webchat:ready;
    3. mai așteptăm 2 secunde;
    4. abia apoi facem restartConversation().
  */
  const RESET_AFTER_OPEN_DELAY = 2000;
  const READY_WAIT_TIMEOUT = 6000;

  let initialized = false;
  let webchatReady = false;
  let webchatOpen = false;

  let resetFinished = false;
  let resetInProgress = false;

  let teaserTimer = null;

  function sleep(ms) {
    return new Promise((resolve) => {
      window.setTimeout(resolve, ms);
    });
  }

  /* ---------------------------------------------------------
     Tipul navigării
     --------------------------------------------------------- */

  function getNavigationType() {
    try {
      const entries =
        performance.getEntriesByType(
          "navigation"
        );

      if (
        entries &&
        entries.length
      ) {
        return entries[0].type;
      }
    } catch {}

    return "";
  }

  /* ---------------------------------------------------------
     Stabilim dacă trebuie conversație nouă
     --------------------------------------------------------- */

  function determineFreshStart() {
    const isReload =
      getNavigationType() ===
      "reload";

    let hadSiteSession =
      false;

    try {
      hadSiteSession =
        sessionStorage.getItem(
          SITE_SESSION_KEY
        ) === "1";

      sessionStorage.setItem(
        SITE_SESSION_KEY,
        "1"
      );
    } catch {
      /*
        Dacă storage-ul nu este disponibil,
        preferăm conversație nouă.
      */
      return true;
    }

    return (
      isReload ||
      !hadSiteSession
    );
  }

  const MUST_START_FRESH =
    determineFreshStart();

  /* ---------------------------------------------------------
     Curățăm doar storage-ul creat de Art Life Design
     --------------------------------------------------------- */

  function clearLegacyArtlifeStorage() {
    const keys = [
      "artlife_chat_transcript_json",
      "artlife_chat_transcript",
      "artlife_chat_conversation",
      "artlife_chat_teaser_seen",
      "artlife_chat_site_session_v3"
    ];

    try {
      keys.forEach((key) => {
        localStorage.removeItem(
          key
        );

        if (
          key !==
          SITE_SESSION_KEY
        ) {
          sessionStorage.removeItem(
            key
          );
        }
      });
    } catch {}
  }

  /* ---------------------------------------------------------
     Butonul propriu de chat
     --------------------------------------------------------- */

  function createLauncher() {
    let launcher =
      document.getElementById(
        "artlifeChatLauncher"
      );

    if (launcher) {
      return launcher;
    }

    launcher =
      document.createElement(
        "button"
      );

    launcher.id =
      "artlifeChatLauncher";

    launcher.className =
      "artlife-chat-launcher";

    launcher.type =
      "button";

    launcher.setAttribute(
      "aria-label",
      "Deschide asistentul virtual Art Life Design"
    );

    launcher.innerHTML = `
      <i
        class="bi bi-chat-dots-fill"
        aria-hidden="true"
      ></i>
    `;

    document.body.appendChild(
      launcher
    );

    launcher.addEventListener(
      "click",
      async (event) => {
        event.preventDefault();

        if (
          webchatOpen &&
          window.botpress &&
          typeof window.botpress
            .close ===
            "function"
        ) {
          window.botpress.close();

          return;
        }

        await openChat();
      }
    );

    return launcher;
  }

  /* ---------------------------------------------------------
     Notificarea / teaser-ul
     --------------------------------------------------------- */

  function createTeaser() {
    let teaser =
      document.getElementById(
        "artlifeChatTeaser"
      );

    if (teaser) {
      return teaser;
    }

    teaser =
      document.createElement(
        "div"
      );

    teaser.id =
      "artlifeChatTeaser";

    teaser.className =
      "artlife-chat-teaser";

    teaser.setAttribute(
      "role",
      "button"
    );

    teaser.setAttribute(
      "tabindex",
      "0"
    );

    teaser.setAttribute(
      "aria-label",
      "Deschide asistentul virtual Art Life Design"
    );

    teaser.innerHTML = `
      <div
        class="artlife-chat-teaser-avatar"
        aria-hidden="true"
      >
        <i class="bi bi-robot"></i>

        <span
          class="artlife-chat-online-dot"
        ></span>
      </div>

      <div
        class="artlife-chat-teaser-copy"
      >
        <strong>
          Salut 👋 Sunt aici să te ajut.
        </strong>

        <span>
          Apasă pentru a deschide conversația.
        </span>
      </div>

      <button
        type="button"
        class="artlife-chat-teaser-close"
        aria-label="Închide notificarea"
      >
        ×
      </button>
    `;

    document.body.appendChild(
      teaser
    );

    const closeButton =
      teaser.querySelector(
        ".artlife-chat-teaser-close"
      );

    const hide = () => {
      teaser.classList.remove(
        "show"
      );

      window.setTimeout(
        () => {
          if (
            !teaser.classList.contains(
              "show"
            )
          ) {
            teaser.style.display =
              "none";
          }
        },
        300
      );
    };

    const show = () => {
      if (webchatOpen) {
        return;
      }

      teaser.style.display =
        "grid";

      requestAnimationFrame(
        () => {
          teaser.classList.add(
            "show"
          );
        }
      );
    };

    const open = async () => {
      hide();

      await openChat();
    };

    teaser.addEventListener(
      "click",
      (event) => {
        if (
          event.target.closest(
            ".artlife-chat-teaser-close"
          )
        ) {
          return;
        }

        open();
      }
    );

    teaser.addEventListener(
      "keydown",
      (event) => {
        if (
          event.key ===
            "Enter" ||
          event.key ===
            " "
        ) {
          event.preventDefault();

          open();
        }
      }
    );

    closeButton?.addEventListener(
      "click",
      (event) => {
        event.preventDefault();
        event.stopPropagation();

        hide();
      }
    );

    window.__artlifeShowChatTeaser =
      show;

    window.__artlifeHideChatTeaser =
      hide;

    return teaser;
  }

  /* ---------------------------------------------------------
     Ascundem launcher-ul nativ Botpress
     --------------------------------------------------------- */

  function hideNativeLauncher() {
    document
      .documentElement
      .classList.add(
        "artlife-custom-chat"
      );
  }

  /* ---------------------------------------------------------
     Așteptăm până când Botpress anunță webchat:ready
     --------------------------------------------------------- */

  async function waitForWebchatReady() {
    const startedAt =
      Date.now();

    while (
      !webchatReady &&
      Date.now() -
        startedAt <
        READY_WAIT_TIMEOUT
    ) {
      await sleep(100);
    }

    return webchatReady;
  }

  /* ---------------------------------------------------------
     Resetarea conversației

     IMPORTANT:
     restartConversation() există DOAR aici.
     Nu se mai apelează în webchat:ready.
     Nu se mai apelează direct în openChat().
     Nu se mai apelează în webchat:opened.
     --------------------------------------------------------- */

  async function restartConversationAfterOpenIfNeeded() {
    if (
      !MUST_START_FRESH ||
      resetFinished ||
      resetInProgress
    ) {
      document.body.classList.remove(
        "artlife-chat-resetting"
      );

      return;
    }

    if (
      !window.botpress ||
      typeof window.botpress
        .restartConversation !==
        "function"
    ) {
      document.body.classList.remove(
        "artlife-chat-resetting"
      );

      return;
    }

    resetInProgress = true;

    try {
      /*
        Așteptăm Botpress ready.
      */
      const ready =
        await waitForWebchatReady();

      if (!ready) {
        throw new Error(
          "Webchat nu a devenit ready în timpul așteptat."
        );
      }

      /*
        IMPORTANT:
        Botpress poate raporta ready înainte
        ca interfața să fie complet montată.

        De aceea mai așteptăm după open().
      */
      await sleep(
        RESET_AFTER_OPEN_DELAY
      );

      await window.botpress
        .restartConversation();

      resetFinished = true;

      console.log(
        "ARTLIFE: conversație Botpress nouă pornită"
      );
    } catch (error) {
      console.warn(
        "Art Life Design: conversația Botpress nu a putut fi resetată.",
        error
      );
    } finally {
      resetInProgress = false;

      /*
        Mic timp pentru ca Webchat să actualizeze UI-ul
        după restart.
      */
      await sleep(250);

      document.body.classList.remove(
        "artlife-chat-resetting"
      );
    }
  }

  /* ---------------------------------------------------------
     Deschiderea chatului
     --------------------------------------------------------- */

  async function openChat() {
    window
      .__artlifeHideChatTeaser?.();

    if (
      !window.botpress ||
      typeof window.botpress
        .open !==
        "function"
    ) {
      return;
    }

    const launcher =
      document.getElementById(
        "artlifeChatLauncher"
      );

    /*
      Dacă este o sesiune nouă / refresh,
      ascundem temporar conținutul chatului
      până terminăm restartul.
    */
    if (
      MUST_START_FRESH &&
      !resetFinished
    ) {
      document.body.classList.add(
        "artlife-chat-resetting"
      );
    }

    launcher?.classList.add(
      "is-hidden"
    );

    /*
      Îl marcăm deschis și manual,
      deoarece webchat:opened nu s-a declanșat
      constant în testele reale.
    */
    webchatOpen = true;

    /*
      Mai întâi deschidem fizic Webchat-ul.
    */
    window.botpress.open();

    /*
      Abia DUPĂ open() încercăm resetarea.
    */
    await restartConversationAfterOpenIfNeeded();
  }

  /* ---------------------------------------------------------
     Inițializare Botpress
     --------------------------------------------------------- */

  function initBotpress() {
    if (initialized) {
      return;
    }

    if (
      !window.botpress ||
      typeof window.botpress
        .init !==
        "function"
    ) {
      window.setTimeout(
        initBotpress,
        120
      );

      return;
    }

    initialized = true;

    clearLegacyArtlifeStorage();

    hideNativeLauncher();

    const launcher =
      createLauncher();

    createTeaser();

    /* -------------------------------------------------------
       WEBCHAT INITIALIZED
       ------------------------------------------------------- */

    window.botpress.on(
      "webchat:initialized",
      () => {
        clearTimeout(
          teaserTimer
        );

        teaserTimer =
          window.setTimeout(
            () => {
              window
                .__artlifeShowChatTeaser?.();
            },
            TEASER_DELAY
          );
      }
    );

    /* -------------------------------------------------------
       WEBCHAT READY

       IMPORTANT:
       aici NU facem restartConversation().
       ------------------------------------------------------- */

    window.botpress.on(
      "webchat:ready",
      () => {
        webchatReady = true;

        console.log(
          "ARTLIFE: webchat ready"
        );
      }
    );

    /* -------------------------------------------------------
       WEBCHAT OPENED

       Îl folosim numai pentru stare/UI.
       NU facem restart aici.
       ------------------------------------------------------- */

    window.botpress.on(
      "webchat:opened",
      () => {
        webchatOpen = true;

        launcher?.classList.add(
          "is-hidden"
        );

        console.log(
          "ARTLIFE: webchat opened"
        );
      }
    );

    /* -------------------------------------------------------
       WEBCHAT CLOSED
       ------------------------------------------------------- */

    window.botpress.on(
      "webchat:closed",
      () => {
        webchatOpen = false;

        document.body.classList.remove(
          "artlife-chat-resetting"
        );

        launcher?.classList.remove(
          "is-hidden"
        );
      }
    );

    /* -------------------------------------------------------
       ERROR
       ------------------------------------------------------- */

    window.botpress.on(
      "error",
      (error) => {
        document.body.classList.remove(
          "artlife-chat-resetting"
        );

        console.warn(
          "Art Life Design / Botpress:",
          error
        );
      }
    );

    /* -------------------------------------------------------
       INIT
       ------------------------------------------------------- */

    window.botpress.init({
      botId: BOT_ID,

      clientId: CLIENT_ID,

      configuration: {
        botName:
          "Art Life Design",

        botDescription:
          "Asistent virtual Art Life Design",

        website: {},

        email: {},

        phone: {},

        termsOfService: {},

        privacyPolicy: {},

        color:
          "#1F4D3A",

        variant:
          "solid",

        themeMode:
          "light",

        fontFamily:
          "inter",

        radius: 2,

        composerPlaceholder:
          "Scrie mesajul tău."
      }
    });
  }

  /* ---------------------------------------------------------
     Pornire
     --------------------------------------------------------- */

  if (
    document.readyState ===
    "loading"
  ) {
    document.addEventListener(
      "DOMContentLoaded",
      initBotpress,
      {
        once: true
      }
    );
  } else {
    initBotpress();
  }
})();