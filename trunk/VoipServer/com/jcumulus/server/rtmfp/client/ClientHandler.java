package com.jcumulus.server.rtmfp.client;

/**
 * jCumulus is a Java port of Cumulus OpenRTMP
 *
 * Copyright 2011 OpenRTMFP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License received along this program for more
 * details (or else see http://www.gnu.org/licenses/).
 *
 *
 * This file is a part of jCumulus.
 */

import com.jcumulus.server.rtmfp.Client;
import com.jcumulus.server.rtmfp.packet.*;
import com.jcumulus.server.rtmfp.application.Publication;
import com.jcumulus.server.rtmfp.application.Listener;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import org.red5.server.webapp.voicebridge.*;
import com.sun.voip.server.*;


public class ClientHandler implements IClientHandler
{
	private static Logger log = LoggerFactory.getLogger(ClientHandler.class);

	public boolean onConnection(Client client, com.jcumulus.server.rtmfp.e.B b, com.jcumulus.server.rtmfp.e.A a)
	{
		log.info("onConnect ");

		return true;
	}

	public void onFailed(Client client, String s)
	{
		log.info("onFailed " + s);
	}

	public void onDisconnection(Client client)
	{
		log.info("onDisconnection ");

	}

	public boolean onPublish(Client client, Publication b)
	{
		log.info("onPublish " + b.C());

		return true;
	}

	public void onUnpublish(Client client, Publication b)
	{
		log.info("onUnPublish " + b.C());
	}

	public boolean onSubscribe(Client client, Listener i)
	{
		log.info("onSubscribe " + i.getPublication().C());

		String publishName = i.getPublication().C();

		if (RtmfpParticipant.publishHandlers.containsKey(publishName) == false)
		{
			RtmfpParticipant.publishHandlers.put(publishName, com.jcumulus.server.rtmfp.d.E.I.getStreams().A(publishName));
		}

		return true;
	}

	public void onUnsubscribe(Client client, Listener i)
	{
		log.info("onUnsubscribe "  + i.getPublication().C());

		String publishName = i.getPublication().C();

		if (RtmfpParticipant.publishHandlers.containsKey(publishName))
		{
			Publication publication = RtmfpParticipant.publishHandlers.remove(publishName);
			publication = null;
		}

	}

	public void onDataPacket(Client client, Publication b, String s, Packet a)
	{
		log.info("onDataPacket ");

	}

	public void onPacket(Client client, Publication b, int i, Packet a)
	{
		if (RtmfpParticipant.playHandlers.containsKey(b.C()))
		{
			//log.info("onPacket " + b.C());
			RtmfpParticipant.playHandlers.get(b.C()).push(a.G());
		}

	}

	public void onVideoPacket(Client client, Publication b, int i, Packet a)
	{
		log.info("onVideoPacket ");

	}

    public void sendDigit(Client client, com.jcumulus.server.rtmfp.e.B b)
    {
        String stream = b.E();
        String digit = b.E();

        Application.component.sendDigit(stream, digit);
    }
}
