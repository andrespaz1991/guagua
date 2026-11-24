import { describe, expect, it } from "vitest";
import { appendTranscript, canSendMessage } from "./conversation";
import { buildTutorMessages, keepMeaningfulCorrection, parseTutorReply, translationTarget } from "./lmstudio";

describe("conversation runtime", () => {
  it("only allows a non-empty message while ISA is idle", () => {
    expect(canSendMessage("I want to practise.", "Idle")).toBe(true);
    expect(canSendMessage("   ", "Idle")).toBe(false);
    expect(canSendMessage("Hello", "Thinking")).toBe(false);
  });

  it("sends the complete recent conversation with the tutor instruction", () => {
    const messages = buildTutorMessages([
      { role: "user", content: "My name is Ana." },
      { role: "assistant", content: "Nice to meet you, Ana." },
      { role: "user", content: "What did I tell you my name was?" },
    ]);
    expect(messages[0].role).toBe("system");
    expect(messages.at(-1)).toEqual({ role: "user", content: "What did I tell you my name was?" });
    expect(messages).toContainEqual({ role: "user", content: "My name is Ana." });
  });

  it("includes stored learner context with each tutor request", () => {
    const messages = buildTutorMessages([{ role: "user", content: "Hello." }], "Name: Ana. Lives in Bogotá.");
    expect(messages).toContainEqual(expect.objectContaining({ role: "system", content: expect.stringContaining("Name: Ana") }));
  });

  it("chooses a useful translation direction", () => {
    expect(translationTarget("I live in Bogotá.")).toBe("Spanish");
    expect(translationTarget("Vivo en Bogotá.")).toBe("English");
  });

  it("keeps the typed text when a new voice transcript arrives", () => {
    expect(appendTranscript("I want to", "practise speaking")).toBe("I want to practise speaking");
  });

  it("separates a model correction from the tutor reply", () => {
    expect(parseTutorReply("Correction: I have been here for two years.\n\nGreat! What do you like about it?")).toEqual({
      correction: "I have been here for two years.",
      text: "Great! What do you like about it?",
    });
  });

  it("removes a no-correction marker instead of showing it to the learner", () => {
    expect(parseTutorReply("**Correction No correction**\n\nNice work. What happened next?")).toEqual({
      text: "Nice work. What happened next?",
    });
  });

  it("does not show a correction when the model repeats the original sentence", () => {
    expect(keepMeaningfulCorrection("Hello ISA, I am testing Enter.", "Hello ISA, I am testing Enter.")).toBeUndefined();
  });
});
