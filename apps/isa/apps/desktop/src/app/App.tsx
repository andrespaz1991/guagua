import { type FormEvent, type KeyboardEvent, useEffect, useMemo, useRef, useState } from "react";
import { appendTranscript, canSendMessage } from "./conversation";
import { checkLmStudio, DEFAULT_LM_STUDIO_MODEL, DEFAULT_LM_STUDIO_URL, keepMeaningfulCorrection, requestEnglishCorrection, requestTranslation, requestTutorReply, translationTarget, type ConversationMessage } from "./lmstudio";
import { speakText, startVoiceInput, supportsVoiceInput, supportsVoiceOutput } from "./speech";
import { deleteConversation, loadBootstrap, loadConversation, saveConversation, type ConversationSummary } from "./storage";

type TutorState = "Idle" | "Recording" | "Thinking" | "Speaking";
type RuntimeState = "checking" | "ready" | "missing-model" | "offline";
type Message = { id: string; role: "isa" | "user"; text: string; correction?: string };

const navigation = ["Conversation", "Practice", "Progress", "History", "Settings"];
const localStateKey = "isa-web-state-v1";
const welcomeMessage = (): Message => ({ id: "isa-welcome", role: "isa", text: "Hi! I’m ISA. Once LM Studio is ready, I can keep track of our conversation." });
const newConversationId = (): string => crypto.randomUUID();
const statusCopy: Record<TutorState, string> = {
  Idle: "ISA is ready to practice.",
  Recording: "Listening carefully…",
  Thinking: "ISA is thinking with your conversation context…",
  Speaking: "ISA is speaking…",
};

export function App() {
  const [activePage, setActivePage] = useState("Conversation");
  const [theme, setTheme] = useState<"dark" | "light">("dark");
  const [tutorState, setTutorState] = useState<TutorState>("Idle");
  const [message, setMessage] = useState("");
  const [serverUrl, setServerUrl] = useState(DEFAULT_LM_STUDIO_URL);
  const [model, setModel] = useState(DEFAULT_LM_STUDIO_MODEL);
  const [profileName, setProfileName] = useState("");
  const [profileContext, setProfileContext] = useState("");
  const [sendOnEnter, setSendOnEnter] = useState(true);
  const [speechLanguage, setSpeechLanguage] = useState("en-US");
  const [stateLoaded, setStateLoaded] = useState(false);
  const [conversationId, setConversationId] = useState(newConversationId);
  const [conversationTitle, setConversationTitle] = useState("New conversation");
  const [savedConversations, setSavedConversations] = useState<ConversationSummary[]>([]);
  const [storageNotice, setStorageNotice] = useState("Connecting local database…");
  const [runtimeState, setRuntimeState] = useState<RuntimeState>("checking");
  const [runtimeNotice, setRuntimeNotice] = useState("Checking LM Studio…");
  const [voiceOutputEnabled, setVoiceOutputEnabled] = useState(true);
  const [voiceInputAvailable, setVoiceInputAvailable] = useState(false);
  const [microphonePermissionRequired, setMicrophonePermissionRequired] = useState(false);
  const [voiceOutputAvailable, setVoiceOutputAvailable] = useState(false);
  const [voices, setVoices] = useState<SpeechSynthesisVoice[]>([]);
  const [englishVoiceUri, setEnglishVoiceUri] = useState("");
  const [spanishVoiceUri, setSpanishVoiceUri] = useState("");
  const [voiceNotice, setVoiceNotice] = useState("Checking voice capabilities…");
  const [liveMode, setLiveMode] = useState(false);
  const [translations, setTranslations] = useState<Record<string, string>>({});
  const [translationError, setTranslationError] = useState<Record<string, string>>({});
  const [loadingTranslationId, setLoadingTranslationId] = useState<string | undefined>(undefined);
  const recognitionRef = useRef<ReturnType<typeof startVoiceInput>>(undefined);
  const speechTimerRef = useRef<number | undefined>(undefined);
  const liveRestartTimerRef = useRef<number | undefined>(undefined);
  const liveSilenceTimerRef = useRef<number | undefined>(undefined);
  const liveTranscriptRef = useRef("");
  const databaseSaveTimerRef = useRef<number | undefined>(undefined);
  const spaceHeldRef = useRef(false);
  const messagesRef = useRef<HTMLDivElement>(null);
  const liveModeRef = useRef(false);
  const liveReplyInFlightRef = useRef(false);
  const [messages, setMessages] = useState<Message[]>([
    welcomeMessage(),
  ]);

  const helperText = useMemo(() => statusCopy[tutorState], [tutorState]);
  const latestIsaMessage = [...messages].reverse().find((entry) => entry.role === "isa");
  const learnerContext = [profileName.trim() && `Name: ${profileName.trim()}.`, profileContext.trim()].filter(Boolean).join(" ");

  useEffect(() => {
    const container = messagesRef.current;
    if (!container) return;
    container.scrollTo({ top: container.scrollHeight, behavior: "smooth" });
  }, [messages]);

  useEffect(() => {
    if (conversationTitle !== "New conversation") return;
    const firstLearnerMessage = messages.find((entry) => entry.role === "user")?.text.trim();
    if (firstLearnerMessage) setConversationTitle(firstLearnerMessage.slice(0, 52));
  }, [messages, conversationTitle]);

  useEffect(() => {
    let cancelled = false;
    async function initialiseStorage() {
      try {
        const stored = localStorage.getItem(localStateKey);
        if (stored) {
          const saved = JSON.parse(stored) as Partial<{ messages: Message[]; profileName: string; profileContext: string; sendOnEnter: boolean; speechLanguage: string; conversationId: string; conversationTitle: string }>;
          if (Array.isArray(saved.messages) && saved.messages.length > 0) setMessages(saved.messages);
          if (typeof saved.profileName === "string") setProfileName(saved.profileName);
          if (typeof saved.profileContext === "string") setProfileContext(saved.profileContext);
          if (typeof saved.sendOnEnter === "boolean") setSendOnEnter(saved.sendOnEnter);
          if (typeof saved.speechLanguage === "string") setSpeechLanguage(saved.speechLanguage);
          if (typeof saved.conversationId === "string") setConversationId(saved.conversationId);
          if (typeof saved.conversationTitle === "string") setConversationTitle(saved.conversationTitle);
        }
      } catch {
        // A damaged browser cache should never block the tutor.
      }

      try {
        const bootstrap = await loadBootstrap();
        if (cancelled) return;
        setProfileName(bootstrap.profile.display_name);
        setProfileContext(bootstrap.profile.learner_context);
        setSavedConversations(bootstrap.conversations);
        if (bootstrap.conversations[0]) {
          const conversation = await loadConversation(bootstrap.conversations[0].id);
          if (cancelled) return;
          setConversationId(conversation.id);
          setConversationTitle(conversation.title);
          setMessages(conversation.messages.length
            ? conversation.messages.map((entry, index) => ({ id: `${conversation.id}-${index}`, role: entry.role, text: entry.body, correction: entry.correction ?? undefined }))
            : [welcomeMessage()]);
        }
        setStorageNotice("Saved in local MySQL database.");
      } catch {
        setStorageNotice("Database is offline; this browser keeps a local review copy.");
      } finally {
        if (!cancelled) setStateLoaded(true);
      }
    }
    void initialiseStorage();
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    const isTypingTarget = (target: EventTarget | null) => target instanceof HTMLElement && ["INPUT", "TEXTAREA", "SELECT", "BUTTON"].includes(target.tagName);
    const onKeyDown = (event: globalThis.KeyboardEvent) => {
      if (event.code !== "Space" || event.repeat || isTypingTarget(event.target) || liveMode || tutorState !== "Idle") return;
      event.preventDefault();
      spaceHeldRef.current = true;
      toggleRecording();
    };
    const onKeyUp = (event: globalThis.KeyboardEvent) => {
      if (event.code !== "Space" || !spaceHeldRef.current) return;
      event.preventDefault();
      spaceHeldRef.current = false;
      if (tutorState === "Recording") toggleRecording();
    };
    window.addEventListener("keydown", onKeyDown);
    window.addEventListener("keyup", onKeyUp);
    return () => {
      window.removeEventListener("keydown", onKeyDown);
      window.removeEventListener("keyup", onKeyUp);
    };
  }, [liveMode, tutorState, voiceInputAvailable, speechLanguage]);

  useEffect(() => {
    if (!stateLoaded) return;
    localStorage.setItem(localStateKey, JSON.stringify({ messages, profileName, profileContext, sendOnEnter, speechLanguage, conversationId, conversationTitle }));
    window.clearTimeout(databaseSaveTimerRef.current);
    databaseSaveTimerRef.current = window.setTimeout(() => {
      void saveConversation({
        conversation: { id: conversationId, title: conversationTitle },
        messages,
        profile: { displayName: profileName, learnerContext: profileContext },
      }).then(() => {
        setStorageNotice("Saved in local MySQL database.");
        setSavedConversations((current) => {
          const updated: ConversationSummary = { id: conversationId, title: conversationTitle, created_at: "", updated_at: new Date().toISOString() };
          return [updated, ...current.filter((entry) => entry.id !== conversationId)];
        });
      }).catch(() => setStorageNotice("Database is offline; this browser keeps a local review copy."));
    }, 650);
    return () => window.clearTimeout(databaseSaveTimerRef.current);
  }, [messages, profileName, profileContext, sendOnEnter, speechLanguage, conversationId, conversationTitle, stateLoaded]);

  useEffect(() => {
    const inputSupported = supportsVoiceInput();
    const outputSupported = supportsVoiceOutput();
    setVoiceInputAvailable(inputSupported);
    setMicrophonePermissionRequired(false);
    setVoiceOutputAvailable(outputSupported);
    setVoiceNotice(inputSupported
      ? "Microphone is ready. Speak in English, then review and send the transcript."
      : "Voice input is not available in this browser. You can still hear ISA’s replies when output is available.");
    void refreshRuntime(DEFAULT_LM_STUDIO_URL, DEFAULT_LM_STUDIO_MODEL);

    const loadVoices = () => setVoices(window.speechSynthesis.getVoices());
    if (outputSupported) {
      loadVoices();
      window.speechSynthesis.addEventListener("voiceschanged", loadVoices);
    }

    return () => {
      recognitionRef.current?.stop();
      window.clearTimeout(speechTimerRef.current);
      window.clearTimeout(liveRestartTimerRef.current);
      window.clearTimeout(liveSilenceTimerRef.current);
      if (outputSupported) window.speechSynthesis.removeEventListener("voiceschanged", loadVoices);
      if (supportsVoiceOutput()) window.speechSynthesis.cancel();
    };
  }, []);

  async function refreshRuntime(server = serverUrl, modelName = model) {
    setRuntimeState("checking");
    setRuntimeNotice("Checking LM Studio…");
    const status = await checkLmStudio(server, modelName);
    if (!status.running) {
      setRuntimeState("offline");
      setRuntimeNotice(`LM Studio is not reachable at ${server}. Start its local server and check the address.`);
    } else if (!status.modelInstalled) {
      setRuntimeState("missing-model");
      setRuntimeNotice(`LM Studio is reachable, but ${modelName} is not available on that server.`);
    } else {
      setRuntimeState("ready");
      setRuntimeNotice(`${modelName} is ready in LM Studio. ISA will use the conversation history.`);
    }
  }

  function finishLiveTurn() {
    window.clearTimeout(liveSilenceTimerRef.current);
    const transcript = liveTranscriptRef.current.trim();
    if (!transcript || liveReplyInFlightRef.current) return;
    liveTranscriptRef.current = "";
    liveReplyInFlightRef.current = true;
    recognitionRef.current?.stop();
    recognitionRef.current = undefined;
    void queueMessage(transcript, true);
  }

  function resumeLiveConversation() {
    if (!liveModeRef.current || !voiceInputAvailable || liveReplyInFlightRef.current) return;
    if (recognitionRef.current) return;
    window.clearTimeout(liveRestartTimerRef.current);
    setTutorState("Recording");
    setVoiceNotice("Live mode is listening…");
    recognitionRef.current = startVoiceInput({
      onTranscript: (transcript) => {
        if (!transcript || liveReplyInFlightRef.current) return;
        liveTranscriptRef.current = appendTranscript(liveTranscriptRef.current, transcript);
        setVoiceNotice("Live mode is listening. Keep speaking; ISA will answer after a short pause…");
        window.clearTimeout(liveSilenceTimerRef.current);
        liveSilenceTimerRef.current = window.setTimeout(finishLiveTurn, 1_800);
      },
      onError: (reason) => {
        const permissionDenied = /not-allowed|service-not-allowed|permission/i.test(reason);
        if (permissionDenied) {
          liveModeRef.current = false;
          setLiveMode(false);
          setMicrophonePermissionRequired(true);
        }
        setVoiceNotice(permissionDenied
          ? `Live mode needs microphone permission: ${reason}.`
          : "Live mode is still active and waiting for your next phrase…");
      },
      onEnd: () => {
        recognitionRef.current = undefined;
        if (liveModeRef.current && !liveReplyInFlightRef.current) {
          if (liveTranscriptRef.current.trim()) return;
          setTutorState("Recording");
          window.clearTimeout(liveRestartTimerRef.current);
          liveRestartTimerRef.current = window.setTimeout(resumeLiveConversation, 900);
          return;
        }
        setTutorState((current) => (current === "Recording" ? "Idle" : current));
      },
    }, { continuous: true, language: speechLanguage });
    if (!recognitionRef.current) setTutorState("Idle");
  }

  function readAloud(text: string, resumeLive = false, language = "en-US") {
    if (!voiceOutputAvailable) {
      setVoiceNotice("Voice output is unavailable in this browser or Windows WebView.");
      return;
    }
    const finishSpeaking = () => {
      window.clearTimeout(speechTimerRef.current);
      setTutorState("Idle");
      if (resumeLive) {
        liveReplyInFlightRef.current = false;
        window.setTimeout(resumeLiveConversation, 180);
      }
    };
    setTutorState("Speaking");
    window.clearTimeout(speechTimerRef.current);
    speechTimerRef.current = window.setTimeout(finishSpeaking, Math.max(3_000, Math.min(15_000, text.length * 75)));
    const started = speakText(text, {
      onStart: () => setTutorState("Speaking"),
      onEnd: finishSpeaking,
      onError: () => {
        setVoiceNotice("ISA could not play this reply. Check Windows audio output and try again.");
        finishSpeaking();
      },
    }, { lang: language, voiceUri: language.startsWith("es") ? spanishVoiceUri : englishVoiceUri });
    if (!started) {
      window.clearTimeout(speechTimerRef.current);
      setTutorState("Idle");
      setVoiceNotice("Voice output is unavailable in this browser or Windows WebView.");
      if (resumeLive) {
        liveReplyInFlightRef.current = false;
        window.setTimeout(resumeLiveConversation, 180);
      }
    }
  }

  function sourceLanguage(text: string): string {
    return translationTarget(text) === "English" ? "es-ES" : "en-US";
  }

  async function queueMessage(rawMessage: string, fromLive = false) {
    const trimmed = rawMessage.trim();
    if ((!fromLive && !canSendMessage(trimmed, tutorState)) || !trimmed) return;

    const userMessage: Message = { id: `user-${Date.now()}`, role: "user", text: trimmed };
    const conversationForModel: ConversationMessage[] = [...messages, userMessage]
      .filter((entry) => entry.id !== "isa-welcome")
      .map((entry) => ({ role: entry.role === "isa" ? "assistant" : "user", content: entry.text }));

    setMessages((current) => [...current, userMessage]);
    if (conversationTitle === "New conversation") setConversationTitle(trimmed.slice(0, 52));
    setMessage("");
    setTutorState("Thinking");
    try {
      const [reply, detectedCorrection] = await Promise.all([
        requestTutorReply(serverUrl, model.trim(), conversationForModel, learnerContext),
        requestEnglishCorrection(serverUrl, model.trim(), trimmed),
      ]);
      const correctionToShow = reply.correction ? keepMeaningfulCorrection(trimmed, reply.correction) ?? detectedCorrection : detectedCorrection;
      setMessages((current) => [...current, { id: `isa-${Date.now()}`, role: "isa", text: reply.text, correction: correctionToShow }]);
      setRuntimeState("ready");
      setRuntimeNotice(`${model} answered using the current conversation context.`);
      if (voiceOutputEnabled) readAloud(correctionToShow ? `${correctionToShow}. ${reply.text}` : reply.text, fromLive);
      else {
        setTutorState("Idle");
        liveReplyInFlightRef.current = false;
        if (fromLive) window.setTimeout(resumeLiveConversation, 180);
      }
    } catch (error) {
      const reason = error instanceof Error ? error.message : "Unknown connection error.";
      setRuntimeState("offline");
      setRuntimeNotice(`ISA could not contact LM Studio (${model}): ${reason}`);
      setTutorState("Idle");
      liveReplyInFlightRef.current = false;
      if (fromLive) window.setTimeout(resumeLiveConversation, 180);
    }
  }

  function sendMessage(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    void queueMessage(message);
  }

  function createNewConversation() {
    setConversationId(newConversationId());
    setConversationTitle("New conversation");
    setMessages([welcomeMessage()]);
    setTranslations({});
    setTranslationError({});
    setActivePage("Conversation");
  }

  async function openSavedConversation(id: string) {
    try {
      const conversation = await loadConversation(id);
      setConversationId(conversation.id);
      setConversationTitle(conversation.title);
      setMessages(conversation.messages.length
        ? conversation.messages.map((entry, index) => ({ id: `${conversation.id}-${index}`, role: entry.role, text: entry.body, correction: entry.correction ?? undefined }))
        : [welcomeMessage()]);
      setTranslations({});
      setTranslationError({});
      setActivePage("Conversation");
      setStorageNotice("Conversation restored from local MySQL database.");
    } catch {
      setStorageNotice("Could not open this conversation while the local database is offline.");
    }
  }

  async function removeSavedConversation(id: string) {
    try {
      await deleteConversation(id);
      setSavedConversations((current) => current.filter((entry) => entry.id !== id));
      if (id === conversationId) createNewConversation();
      setStorageNotice("Conversation deleted from the local database.");
    } catch {
      setStorageNotice("Could not delete this conversation while the local database is offline.");
    }
  }

  function handleComposerKeyDown(event: KeyboardEvent<HTMLTextAreaElement>) {
    if (sendOnEnter && event.key === "Enter" && !event.shiftKey && canSendMessage(message, tutorState)) {
      event.preventDefault();
      void queueMessage(message);
    }
  }

  function toggleLiveMode() {
    if (liveModeRef.current) {
      liveModeRef.current = false;
      liveReplyInFlightRef.current = false;
      setLiveMode(false);
      recognitionRef.current?.stop();
      recognitionRef.current = undefined;
      window.clearTimeout(liveRestartTimerRef.current);
      window.clearTimeout(liveSilenceTimerRef.current);
      liveTranscriptRef.current = "";
      if (supportsVoiceOutput()) window.speechSynthesis.cancel();
      setTutorState("Idle");
      setVoiceNotice("Live mode is off.");
      return;
    }
    if (!voiceInputAvailable || !voiceOutputAvailable) {
      setVoiceNotice("Live mode needs microphone input and voice output in this browser.");
      return;
    }
    liveModeRef.current = true;
    setLiveMode(true);
    resumeLiveConversation();
  }

  async function toggleTranslation(translationId: string, text: string) {
    if (translations[translationId]) {
      setTranslations((current) => {
        const next = { ...current };
        delete next[translationId];
        return next;
      });
      return;
    }

    setLoadingTranslationId(translationId);
    setTranslationError((current) => {
      const next = { ...current };
      delete next[translationId];
      return next;
    });
    try {
      const translation = await requestTranslation(serverUrl, model.trim(), text);
      setTranslations((current) => ({ ...current, [translationId]: translation }));
    } catch (error) {
      const reason = error instanceof Error ? error.message : "Unknown connection error.";
      setTranslationError((current) => ({ ...current, [translationId]: `Translation unavailable: ${reason}` }));
    } finally {
      setLoadingTranslationId(undefined);
    }
  }

  function toggleRecording() {
    if (tutorState === "Recording") {
      recognitionRef.current?.stop();
      recognitionRef.current = undefined;
      setTutorState("Idle");
      setVoiceNotice("Voice input stopped. You can edit the transcript before sending it.");
      return;
    }
    if (!voiceInputAvailable) {
      setVoiceNotice("Voice input needs Chrome or a WebView that supports Speech Recognition. Type your answer or use the desktop provider in a later phase.");
      return;
    }

    setTutorState("Recording");
    setVoiceNotice("Listening in English…");
    recognitionRef.current = startVoiceInput({
      onTranscript: (transcript) => {
        setMessage((current) => appendTranscript(current, transcript));
        setVoiceNotice("Transcript added. Keep speaking or press Stop listening when you are done.");
      },
      onError: (reason) => {
        if (/not-allowed|service-not-allowed|permission/i.test(reason)) setMicrophonePermissionRequired(true);
        setVoiceNotice(`Voice input stopped: ${reason}. Check microphone permission and try again.`);
      },
      onEnd: () => {
        recognitionRef.current = undefined;
        setTutorState((current) => (current === "Recording" ? "Idle" : current));
      },
    }, { continuous: true, language: speechLanguage });
  }

  return (
    <main className={`app-shell theme-${theme}`}>
      <aside className="sidebar" aria-label="Primary navigation">
        <div className="brand"><span className="brand-mark" aria-hidden="true">I</span><span>ISA</span></div>
        <nav>{navigation.map((item) => <button className={activePage === item ? "nav-item active" : "nav-item"} key={item} onClick={() => setActivePage(item)} type="button">{item}</button>)}</nav>
        <div className="sidebar-footer"><span className={`offline-dot ${runtimeState}`} aria-hidden="true" />{runtimeState === "ready" ? "Local model ready" : "Local model setup"}</div>
      </aside>

      <section className="workspace" aria-live="polite">
        <header className="topbar">
          <div><p className="eyebrow">{activePage}</p><h1>{activePage === "Conversation" ? "Talk with ISA" : activePage}</h1></div>
          <button className="theme-toggle" onClick={() => setTheme((current) => (current === "dark" ? "light" : "dark"))} type="button">{theme === "dark" ? "Light theme" : "Dark theme"}</button>
        </header>
        <div className="conversation-layout">
          <section className="conversation-card" aria-label="Conversation with ISA">
            <div className="tutor-status"><span className={`status-orb ${tutorState.toLowerCase()}`} aria-hidden="true" /><div><strong>ISA · {tutorState}</strong><p>{helperText}</p></div></div>
            <div className="messages" ref={messagesRef}>
              {messages.map((entry) => (
                <article className={`message ${entry.role}`} key={entry.id}>
                  <span className="message-label">{entry.role === "isa" ? "ISA" : "You"}</span>
                  <p>{entry.text}</p>
                  <div className="message-actions">
                    <button className="translation-toggle" disabled={loadingTranslationId === entry.id} onClick={() => void toggleTranslation(entry.id, entry.text)} type="button">
                      {loadingTranslationId === entry.id ? "Translating…" : translations[entry.id] ? "Hide translation" : "Ver traducción"}
                    </button>
                    <button className="read-message" disabled={!voiceOutputAvailable || tutorState === "Speaking"} onClick={() => readAloud(entry.text, false, sourceLanguage(entry.text))} type="button">Read aloud</button>
                  </div>
                  {translations[entry.id] && (
                    <div className="translation">
                      <span>Traducción</span>
                      <p>{translations[entry.id]}</p>
                      <button className="read-translation" disabled={!voiceOutputAvailable || tutorState === "Speaking"} onClick={() => readAloud(translations[entry.id], false, translationTarget(entry.text) === "Spanish" ? "es-ES" : "en-US")} type="button">Escuchar traducción</button>
                    </div>
                  )}
                  {translationError[entry.id] && <p className="translation-error" role="status">{translationError[entry.id]}</p>}
                  {entry.correction && (
                    <div className="correction">
                      <span>Correction</span>
                      <p>{entry.correction}</p>
                      <div className="message-actions">
                        <button className="translation-toggle" disabled={loadingTranslationId === `${entry.id}-correction`} onClick={() => void toggleTranslation(`${entry.id}-correction`, entry.correction ?? "")} type="button">
                          {loadingTranslationId === `${entry.id}-correction` ? "Translating…" : translations[`${entry.id}-correction`] ? "Hide translation" : "Ver traducción"}
                        </button>
                        <button className="read-message" disabled={!voiceOutputAvailable || tutorState === "Speaking"} onClick={() => readAloud(entry.correction ?? "", false, "en-US")} type="button">Read aloud</button>
                      </div>
                      {translations[`${entry.id}-correction`] && (
                        <div className="translation">
                          <span>Traducción</span>
                          <p>{translations[`${entry.id}-correction`]}</p>
                          <button className="read-translation" disabled={!voiceOutputAvailable || tutorState === "Speaking"} onClick={() => readAloud(translations[`${entry.id}-correction`], false, "es-ES")} type="button">Escuchar traducción</button>
                        </div>
                      )}
                      {translationError[`${entry.id}-correction`] && <p className="translation-error" role="status">{translationError[`${entry.id}-correction`]}</p>}
                    </div>
                  )}
                </article>
              ))}
            </div>
            <form className="composer" onSubmit={sendMessage}>
              <label className="sr-only" htmlFor="message">Message ISA</label>
              <textarea disabled={tutorState !== "Idle"} id="message" onChange={(event) => setMessage(event.target.value)} onKeyDown={handleComposerKeyDown} placeholder="Write in English or Spanish…" value={message} />
              <div className="composer-actions">
                <button aria-pressed={tutorState === "Recording"} className={tutorState === "Recording" ? "microphone recording" : "microphone"} disabled={tutorState === "Thinking" || tutorState === "Speaking" || liveMode} onClick={toggleRecording} type="button">{tutorState === "Recording" ? "Stop listening" : "Voice input"}</button>
                <button className="clear" disabled={!message} onClick={() => setMessage("")} type="button">Clear</button>
                <button className="send" disabled={!canSendMessage(message, tutorState)} type="submit">Send</button>
              </div>
              <p className="voice-notice" role="status">{voiceNotice}</p>
            </form>
          </section>
          <aside className="learning-panel" aria-label="Tutor controls and learning overview">
            <p className="eyebrow">Local AI</p><h2>Conversation model</h2>
            <label className="model-label" htmlFor="lmstudio-url">LM Studio address</label>
            <input className="model-input" id="lmstudio-url" onChange={(event) => setServerUrl(event.target.value)} value={serverUrl} />
            <label className="model-label model-label-spaced" htmlFor="lmstudio-model">Loaded model</label>
            <input className="model-input" id="lmstudio-model" onChange={(event) => setModel(event.target.value)} value={model} />
            <button className="runtime-check" disabled={runtimeState === "checking"} onClick={() => void refreshRuntime()} type="button">Check LM Studio</button>
            <p className={`runtime-notice ${runtimeState}`} role="status">{runtimeNotice}</p>
            <p className="eyebrow voice-eyebrow">Preferences</p>
            <label className="setting-toggle"><input checked={sendOnEnter} onChange={(event) => setSendOnEnter(event.target.checked)} type="checkbox" />Send with Enter <small>Use Shift+Enter for a new line.</small></label>
            <label className="model-label model-label-spaced" htmlFor="speech-language">Speech recognition language</label>
            <select className="voice-select" id="speech-language" onChange={(event) => setSpeechLanguage(event.target.value)} value={speechLanguage}>
              <option value="en-US">English (United States)</option>
              <option value="en-GB">English (United Kingdom)</option>
              <option value="es-ES">Spanish (Spain)</option>
              <option value="es-MX">Spanish (Mexico)</option>
            </select>
            <p className="eyebrow profile-eyebrow">Personal context</p>
            <label className="model-label" htmlFor="profile-name">Name</label>
            <input className="model-input" id="profile-name" onChange={(event) => setProfileName(event.target.value)} placeholder="e.g. Andrés" value={profileName} />
            <label className="model-label model-label-spaced" htmlFor="profile-context">What ISA should remember</label>
            <textarea className="profile-context" id="profile-context" onChange={(event) => setProfileContext(event.target.value)} placeholder="Goals, level, interests, preferred topics…" value={profileContext} />
            <p className="profile-note">Saved locally in this browser and sent to your LM Studio model with each reply.</p>
            <div className="conversation-library">
              <div className="library-heading"><span>Saved conversations</span><button onClick={createNewConversation} type="button">New</button></div>
              <label className="model-label model-label-spaced" htmlFor="conversation-title">Current conversation title</label>
              <input className="model-input" id="conversation-title" onChange={(event) => setConversationTitle(event.target.value.slice(0, 160))} value={conversationTitle} />
              <p className="storage-notice" role="status">{storageNotice}</p>
              <div className="conversation-list">
                {savedConversations.map((conversation) => (
                  <div className={conversation.id === conversationId ? "conversation-item active" : "conversation-item"} key={conversation.id}>
                    <button onClick={() => void openSavedConversation(conversation.id)} type="button">{conversation.title}</button>
                    <button aria-label={`Delete ${conversation.title}`} className="delete-conversation" onClick={() => void removeSavedConversation(conversation.id)} type="button">×</button>
                  </div>
                ))}
              </div>
            </div>
            <p className="eyebrow voice-eyebrow">Voice mode</p><h2>Listen and reply</h2>
            <button aria-pressed={liveMode} className="live-toggle" disabled={!voiceInputAvailable || !voiceOutputAvailable} onClick={toggleLiveMode} type="button">Live mode: {liveMode ? "on" : "off"}</button>
            <p className="voice-mode-note">When on, ISA listens again after every voice reply until you turn it off.</p>
            <button aria-pressed={voiceOutputEnabled} className="voice-toggle" disabled={!voiceOutputAvailable} onClick={() => setVoiceOutputEnabled((current) => !current)} type="button">Voice replies: {voiceOutputEnabled ? "on" : "off"}</button>
            <button className="read-reply" disabled={!latestIsaMessage || !voiceOutputAvailable || tutorState === "Speaking"} onClick={() => latestIsaMessage && readAloud(latestIsaMessage.text, false, sourceLanguage(latestIsaMessage.text))} type="button">Read latest reply</button>
            <div className="voice-capabilities"><span>Microphone: {!voiceInputAvailable ? "not available" : microphonePermissionRequired ? "permission required" : "available"}</span><span>Speaker: {voiceOutputAvailable ? "available" : "not available"}</span></div>
            <div className="voice-settings">
              <label className="model-label" htmlFor="english-voice">ISA voice</label>
              <select className="voice-select" id="english-voice" onChange={(event) => setEnglishVoiceUri(event.target.value)} value={englishVoiceUri}>
                <option value="">Default — original ISA voice</option>
                {voices.filter((voice) => voice.lang.toLowerCase().startsWith("en")).map((voice) => <option key={voice.voiceURI} value={voice.voiceURI}>{voice.name} ({voice.lang})</option>)}
              </select>
              <label className="model-label model-label-spaced" htmlFor="spanish-voice">Translation voice</label>
              <select className="voice-select" id="spanish-voice" onChange={(event) => setSpanishVoiceUri(event.target.value)} value={spanishVoiceUri}>
                <option value="">Default — original ISA voice</option>
                {voices.filter((voice) => voice.lang.toLowerCase().startsWith("es")).map((voice) => <option key={voice.voiceURI} value={voice.voiceURI}>{voice.name} ({voice.lang})</option>)}
              </select>
            </div>
          </aside>
        </div>
      </section>
    </main>
  );
}
