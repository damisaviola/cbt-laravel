import Dexie from 'dexie';

// Initialize Dexie database for offline-first exam storage
export class SmartCBTDatabase extends Dexie {
    constructor() {
        super('SmartCBT_ClientDB');
        this.version(1).stores({
            // schema: primary key, indexes
            attempts: 'attemptId, sessionToken, status, updatedAt',
            answers: '[attemptId+questionId], attemptId, questionId, isSynced, updatedAt',
            offlineQueue: '++id, attemptId, questionId, timestamp',
        });
    }
}

export const localDb = new SmartCBTDatabase();

/**
 * Save an answer locally into IndexedDB with instant latency (< 10ms)
 */
export async function saveAnswerLocally(attemptId, questionId, data) {
    const record = {
        attemptId: Number(attemptId),
        questionId: Number(questionId),
        selectedOptions: data.selectedOptions || [],
        essayText: data.essayText || '',
        isDoubt: Boolean(data.isDoubt),
        isSynced: false,
        updatedAt: Date.now(),
    };

    await localDb.answers.put(record);
    return record;
}

/**
 * Retrieve all local answers for a given attempt
 */
export async function getLocalAnswers(attemptId) {
    const list = await localDb.answers.where({ attemptId: Number(attemptId) }).toArray();
    const map = {};
    for (const ans of list) {
        map[ans.questionId] = ans;
    }
    return map;
}

/**
 * Mark a list of question answers as synced
 */
export async function markAnswersAsSynced(attemptId, questionIds) {
    await localDb.transaction('rw', localDb.answers, async () => {
        for (const qid of questionIds) {
            await localDb.answers.where({ attemptId: Number(attemptId), questionId: Number(qid) }).modify({ isSynced: true });
        }
    });
}

/**
 * Get all unsynced answers ready for batch sync
 */
export async function getUnsyncedAnswers(attemptId) {
    return await localDb.answers
        .where({ attemptId: Number(attemptId) })
        .filter(ans => ans.isSynced === false)
        .toArray();
}

/**
 * Clear local data for finished attempt
 */
export async function clearLocalAttempt(attemptId) {
    await localDb.answers.where({ attemptId: Number(attemptId) }).delete();
}
