type RecognitionResultEvent = Event & {
  results: { length: number; [index: number]: { [index: number]: { transcript: string } } };
};

type RecognitionInstance = {
  continuous: boolean;
  interimResults: boolean;
  lang: string;
  maxAlternatives: number;
  start: () => void;
  stop: () => void;
  onresult: ((event: RecognitionResultEvent) => void) | null;
  onerror: ((event: Event & { error?: string }) => void) | null;
  onend: (() => void) | null;
};

type RecognitionConstructor = new () => RecognitionInstance;

function getRecognitionConstructor(): RecognitionConstructor | undefined {
  const browserWindow = window as Window & {
    SpeechRecognition?: RecognitionConstructor;
    webkitSpeechRecognition?: RecognitionConstructor;
  };
  return browserWindow.SpeechRecognition ?? browserWindow.webkitSpeechRecognition;
}

export function supportsVoiceInput(): boolean {
  return typeof window !== "undefined" && Boolean(getRecognitionConstructor());
}

export function supportsVoiceOutput(): boolean {
  return typeof window !== "undefined" && "speechSynthesis" in window;
}

export function startVoiceInput(handlers: {
  onTranscript: (transcript: string) => void;
  onError: (reason: string) => void;
  onEnd: () => void;
}, options: { continuous?: boolean; language?: string } = {}): RecognitionInstance | undefined {
  const Recognition = getRecognitionConstructor();
  if (!Recognition) return undefined;

  const recognition = new Recognition();
  recognition.continuous = options.continuous ?? false;
  recognition.interimResults = false;
  recognition.lang = options.language ?? "en-US";
  recognition.maxAlternatives = 3;
  recognition.onresult = (event) => {
    const latestResult = event.results[event.results.length - 1];
    handlers.onTranscript(latestResult[0].transcript.trim());
  };
  recognition.onerror = (event) => handlers.onError(event.error ?? "unknown error");
  recognition.onend = handlers.onEnd;
  try {
    recognition.start();
  } catch (error) {
    handlers.onError(error instanceof Error ? error.message : "could not start microphone");
    handlers.onEnd();
    return undefined;
  }
  return recognition;
}

export function speakText(
  text: string,
  handlers: { onStart: () => void; onEnd: () => void; onError: () => void },
  options: { lang?: string; voiceUri?: string } = {},
): boolean {
  if (!supportsVoiceOutput()) return false;
  window.speechSynthesis.cancel();
  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = options.lang ?? "en-US";
  if (options.voiceUri) {
    const selectedVoice = window.speechSynthesis.getVoices().find((voice) => voice.voiceURI === options.voiceUri);
    if (selectedVoice) utterance.voice = selectedVoice;
  }
  utterance.rate = 0.92;
  utterance.onstart = handlers.onStart;
  utterance.onend = handlers.onEnd;
  utterance.onerror = handlers.onError;
  window.speechSynthesis.speak(utterance);
  return true;
}
