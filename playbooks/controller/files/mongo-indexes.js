/**
 * Adds indexes to mongo tables.
 */

db.course.ensureIndex({ id: 1 });
db.cache.ensureIndex({ id: 1 });
