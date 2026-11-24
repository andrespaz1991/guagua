export function canSendMessage(message: string, tutorState: string): boolean {
  return message.trim().length > 0 && tutorState === "Idle";
}

export function appendTranscript(currentText: string, transcript: string): string {
  const existing = currentText.trim();
  const addition = transcript.trim();
  if (!existing) return addition;
  if (!addition) return existing;
  return `${existing} ${addition}`;
}
