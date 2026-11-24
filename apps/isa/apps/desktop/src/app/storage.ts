const apiUrl = "http://127.0.0.1/guagua/apps/isa/api/index.php";

export type StoredMessage = { role: "isa" | "user"; text: string; correction?: string };
export type ConversationSummary = { id: string; title: string; created_at: string; updated_at: string };

export async function loadBootstrap(): Promise<{ profile: { display_name: string; learner_context: string }; conversations: ConversationSummary[] }> {
  const response = await fetch(`${apiUrl}?action=bootstrap`);
  if (!response.ok) throw new Error("Database unavailable");
  return response.json() as Promise<{ profile: { display_name: string; learner_context: string }; conversations: ConversationSummary[] }>;
}

export async function loadConversation(id: string): Promise<{ id: string; title: string; messages: Array<{ role: "isa" | "user"; body: string; correction: string | null }> }> {
  const response = await fetch(`${apiUrl}?action=conversation&id=${encodeURIComponent(id)}`);
  if (!response.ok) throw new Error("Could not load conversation");
  return response.json() as Promise<{ id: string; title: string; messages: Array<{ role: "isa" | "user"; body: string; correction: string | null }> }>;
}

export async function saveConversation(payload: {
  conversation: { id: string; title: string };
  messages: StoredMessage[];
  profile: { displayName: string; learnerContext: string };
}): Promise<void> {
  const response = await fetch(`${apiUrl}?action=save`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error("Could not save conversation");
}

export async function deleteConversation(id: string): Promise<void> {
  const response = await fetch(`${apiUrl}?action=conversation&id=${encodeURIComponent(id)}`, { method: "DELETE" });
  if (!response.ok) throw new Error("Could not delete conversation");
}
