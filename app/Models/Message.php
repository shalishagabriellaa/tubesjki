<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
        'read_at',
        'sent_at',
        'deleted_by_sender',
        'deleted_by_receiver',
        'message_type',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'deleted_by_sender' => 'boolean',
        'deleted_by_receiver' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constants for message types
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';

    /**
     * Get the sender of the message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Boot method to set default values
     */
    protected static function booted()
    {
        static::creating(function ($message) {
            if (empty($message->sent_at)) {
                $message->sent_at = now();
            }
        });
    }

    /**
     * Scope to get messages between two users
     */
    public function scopeBetweenUsers(Builder $query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }

    /**
     * Scope to get unread messages for a user
     */
    public function scopeUnreadFor(Builder $query, $userId)
    {
        return $query->where('receiver_id', $userId)->where('is_read', false);
    }

    /**
     * Scope to get messages not deleted by user
     */
    public function scopeNotDeletedBy(Builder $query, $userId, $role = null)
    {
        if ($role === 'sender') {
            return $query->where('deleted_by_sender', false);
        } elseif ($role === 'receiver') {
            return $query->where('deleted_by_receiver', false);
        }
        
        return $query->where(function ($q) use ($userId) {
            $q->where(function ($subQ) use ($userId) {
                $subQ->where('sender_id', $userId)->where('deleted_by_sender', false);
            })->orWhere(function ($subQ) use ($userId) {
                $subQ->where('receiver_id', $userId)->where('deleted_by_receiver', false);
            });
        });
    }

    /**
     * Scope to get messages by type
     */
    public function scopeByType(Builder $query, $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
        return $this;
    }

    /**
     * Delete message for sender
     */
    public function deleteForSender()
    {
        $this->update(['deleted_by_sender' => true]);
        return $this;
    }

    /**
     * Delete message for receiver
     */
    public function deleteForReceiver()
    {
        $this->update(['deleted_by_receiver' => true]);
        return $this;
    }

    /**
     * Delete message for specific user
     */
    public function deleteForUser($userId)
    {
        if ($this->sender_id == $userId) {
            return $this->deleteForSender();
        } elseif ($this->receiver_id == $userId) {
            return $this->deleteForReceiver();
        }
        return false;
    }

    /**
     * Check if message is deleted by both users
     */
    public function isDeletedByBoth()
    {
        return $this->deleted_by_sender && $this->deleted_by_receiver;
    }

    /**
     * Check if message is visible to user
     */
    public function isVisibleTo($userId)
    {
        if ($this->sender_id == $userId) {
            return !$this->deleted_by_sender;
        } elseif ($this->receiver_id == $userId) {
            return !$this->deleted_by_receiver;
        }
        return false;
    }

    /**
     * Get encrypted content
     */
    public function getEncryptedContentAttribute()
    {
        return Crypt::encryptString($this->content);
    }

    /**
     * Set encrypted content
     */
    public function setEncryptedContentAttribute($value)
    {
        $this->attributes['content'] = Crypt::decryptString($value);
    }

    /**
     * Get message type options
     */
    public static function getMessageTypes()
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_FILE => 'File',
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_VIDEO => 'Video',
        ];
    }

    /**
     * Check if message type is file-based
     */
    public function isFileType()
    {
        return in_array($this->message_type, [
            self::TYPE_IMAGE,
            self::TYPE_FILE,
            self::TYPE_AUDIO,
            self::TYPE_VIDEO
        ]);
    }

    /**
     * Get file URL from metadata
     */
    public function getFileUrl()
    {
        if ($this->isFileType() && isset($this->metadata['file_path'])) {
            return asset('storage/' . $this->metadata['file_path']);
        }
        return null;
    }

    /**
     * Get file name from metadata
     */
    public function getFileName()
    {
        return $this->metadata['file_name'] ?? null;
    }

    /**
     * Get file size from metadata
     */
    public function getFileSize()
    {
        return $this->metadata['file_size'] ?? null;
    }

    /**
     * Format sent at time
     */
    public function getSentAtFormattedAttribute()
    {
        return $this->sent_at ? $this->sent_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * Format read at time
     */
    public function getReadAtFormattedAttribute()
    {
        return $this->read_at ? $this->read_at->format('Y-m-d H:i:s') : null;
    }

    /**
     * Get time ago format
     */
    public function getSentAtHumanAttribute()
    {
        return $this->sent_at ? $this->sent_at->diffForHumans() : null;
    }

    /**
     * Check if message was sent today
     */
    public function isSentToday()
    {
        return $this->sent_at && $this->sent_at->isToday();
    }

    /**
     * Get conversation partner for a user
     */
    public function getConversationPartner($userId)
    {
        return $this->sender_id == $userId ? $this->receiver : $this->sender;
    }
}
