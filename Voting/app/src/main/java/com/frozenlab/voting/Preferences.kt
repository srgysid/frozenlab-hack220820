package com.frozenlab.voting

import android.content.Context

object Preferences {

    private const val PREF_FILE = "com.frozenlab.voting.preferences"
    private val sharedPreferences = VotingApplication.appContext.getSharedPreferences(
        PREF_FILE, Context.MODE_PRIVATE)

    private enum class PreferencesValues(val prefName: String, val defaultValue: Any) {
        ACCESS_TOKEN( "PREF_ACCESS_TOKEN", "" ),
        FCM_TOKEN(    "PREF_FCM_TOKEN",    "" ),
    }

    // Settings
    const val baseURL:        String = "https://api-golos.tochno.live"
    const val jsonDateFormat: String = "yyyy-MM-dd HH:mm:ssZ"

    var accessToken: String
        get()      = getStringValue(PreferencesValues.ACCESS_TOKEN)
        set(value) = setStringValue(PreferencesValues.ACCESS_TOKEN, value)

    var fcmToken: String
        get()      = getStringValue(PreferencesValues.FCM_TOKEN)
        set(value) = setStringValue(PreferencesValues.FCM_TOKEN, value)

    // Private area

    private fun getStringValue(pref: PreferencesValues): String {
        return sharedPreferences.getString(pref.prefName, pref.defaultValue as String) ?: ""
    }

    private fun getBooleanValue(pref: PreferencesValues): Boolean {
        return sharedPreferences.getBoolean(pref.prefName, pref.defaultValue as Boolean)
    }

    private fun getLongValue(pref: PreferencesValues): Long {
        return sharedPreferences.getLong(pref.prefName, pref.defaultValue as Long)
    }

    private fun setStringValue(pref: PreferencesValues, value: String) {

        sharedPreferences
            .edit()
            .putString(pref.prefName, value)
            .apply()
    }

    private fun setBooleanValue(pref: PreferencesValues, value: Boolean) {

        sharedPreferences
            .edit()
            .putBoolean(pref.prefName, value)
            .apply()
    }

    private fun setLongValue(pref: PreferencesValues, value: Long) {
        sharedPreferences
            .edit()
            .putLong(pref.prefName, value)
            .apply()
    }
}
